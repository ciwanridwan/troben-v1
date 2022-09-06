<?php

namespace App\Http\Controllers\Api\Partner;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Partner\PartnerNearbyResource;
use App\Http\Resources\Api\Partner\PartnerResource;
use App\Models\Partners\Partner;
use App\Supports\DistanceMatrix;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PartnerController extends Controller
{
    /**
     * Filtered attributes.
     *
     * @var array
     */
    protected array $attributes;

    /**
     * Get Type of Transporter List
     * Route Path       : {API_DOMAIN}/partner/list
     * Route Name       : api.partner.list
     * Route Method     : GET.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function list(Request $request): JsonResponse
    {
        $this->attributes = Validator::make($request->all(), [
            'type' => 'nullable',
            'origin' => 'nullable',
        ])->validate();

        return $this->getPartnerData();
    }

    /**
     * Get Type of Transporter Nearby
     * Route Path       : {API_DOMAIN}/partner/nearby
     * Route Name       : api.partner.nearby
     * Route Method     : GET.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function nearby(Request $request): JsonResponse
    {
        $this->attributes = Validator::make($request->all(), [
            'type' => 'nullable',
            'origin' => 'nullable',
            'page' => 'nullable',
            'lat' => 'required|numeric',
            'lon' => 'required|numeric',
        ])->validate();

        $w = [];
        if ($request->has('origin')) {
            $w[] = sprintf(" AND p.geo_regency_id = '%s'", $request->get('origin'));
        }
        if ($request->has('id')) {
            $w[] = sprintf(" AND p.id = '%s'", $request->get('id'));
        }
        if ($request->has('q')) {
            $q = $request->get('q');
            $w[] = sprintf(" AND (p.name LIKE '%%%s%%' OR p.code ILIKE '%%%s%%')", $q, $q);
        }
        if ($request->has('type')) {
            $w[] = sprintf(" AND EXISTS (SELECT * FROM transporters t WHERE t.partner_id = p.id AND type ILIKE '%s%s%s' AND t.deleted_at IS NULL)", '%', $request->get('type'), '%');
        }

        $lat = $request->get('lat');
        $lon = $request->get('lon');
        $origin = sprintf('%f,%f', $lat, $lon);
        $limit = 5;
        $page = (int) $request->get('page');
        if ($page > 0) {
            $offset = sprintf('OFFSET %d', $page * $limit);
        } else {
            $offset = '';
        }

        $q = "SELECT p.id, p.longitude, p.latitude,
            6371 * acos(cos(radians(%f)) * cos(radians(latitude::FLOAT)) 
                * cos(radians(longitude::FLOAT) - radians(%f))
                + sin(radians(%f))
                * sin(radians(latitude::FLOAT))) AS distance_radian
        FROM partners p
        WHERE p.type = '%s'
            AND latitude IS NOT NULL
            AND longitude IS NOT NULL
            %s
        ORDER BY distance_radian
        LIMIT %d %s";

        // $q = sprintf($q, $lat, $lon, $lat, Partner::TYPE_BUSINESS, implode(' ', $w));
        $q = sprintf($q, $lat, $lon, $lat, Partner::TYPE_BUSINESS, implode(' ', $w), $limit, $offset);
        $nearby = collect(DB::select($q))->map(function ($r) use ($origin) {
            $destination = sprintf('%f,%f', $r->latitude, $r->longitude);
            $distance = DistanceMatrix::calculateDistance($origin, $destination);

            $r->distance_matrix = $distance;
            return $r;
        });

        $result = Partner::query()
            ->whereIn('id', $nearby->pluck('id'))
            ->get()
            ->map(function ($r) use ($nearby) {
                $dr = 0;
                $dm = 0;
                $dist = $nearby->where('id', $r->id)->first();
                if ($dist) {
                    $dr = $dist->distance_radian;
                    $dm = $dist->distance_matrix;
                }
                $r->distance_radian = $dr;
                $r->distance_matrix = $dm;
                return $r;
            })->sortBy('distance_matrix')->values();

        return $this->jsonSuccess(PartnerNearbyResource::collection($result));
    }

    protected function getPartnerData(): JsonResponse
    {
        $query = $this->getBasicBuilder(Partner::query());

        // MITRA MB
        $query->where('type', Partner::TYPE_BUSINESS);
        $query->whereNotNull(['latitude','longitude']);

        $query->when(request()->has('type'), fn ($q) => $q->whereHas('transporters', function (Builder $query) {
            $query->where('type', 'like', $this->attributes['type']);
        }));
        $query->when(request()->has('origin'), fn ($q) => $q->where('geo_regency_id', $this->attributes['origin']));

        return $this->jsonSuccess(PartnerResource::collection($query->get()));
    }

    /**
     * Get Basic Builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function getBasicBuilder(Builder $builder): Builder
    {
        $builder->when(request()->has('id'), fn ($q) => $q->where('id', $this->attributes['id']));
        $builder->when(
            request()->has('q') and request()->has('id') === false,
            fn ($q) => $q->where('name', 'like', '%'.$this->attributes['q'].'%')
        );

        return $builder;
    }
}
