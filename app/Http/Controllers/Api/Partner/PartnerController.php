<?php

namespace App\Http\Controllers\Api\Partner;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Partner\PartnerResource;
use App\Http\Resources\Geo\RegencyResource;
use App\Models\Geo\Regency;
use App\Models\Partners\Partner;
use App\Models\Partners\Transporter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PartnerController extends Controller
{
    /**
     * Filtered attributes.
     *
     * @var array
     */
    protected $attributes;

    /**
     * Get Type of Transporter List
     * Route Path       : {API_DOMAIN}/partner
     * Route Name       : api.partner
     * Route Method     : GET.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function list(Request $request): JsonResponse
    {
        $this->attributes = Validator::make($request->all(), [
           /* 'type' => ['nullable', Rule::in([
                'bike'
            ])],*/
            'type' => 'required',
            'origin' => 'nullable',
        ])->validate();

        return $this->getPartnerData();
    }

    protected function getPartnerData(): JsonResponse
    {

        $query = $this->getBasicBuilder(Partner::query());
        $query->when(request()->has('type'), fn ($q) => $q->whereHas('transporters', function (Builder $query) {
            $query->where('type', 'like', $this->attributes['type']);
        }));
        $query->when(request()->has('origin'), fn ($q) => $q->where('geo_regency_id', $this->attributes['origin']));

        return $this->jsonSuccess(PartnerResource::collection($query->paginate(request('per_page', 15))));
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
