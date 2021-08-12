<?php

namespace App\Http\Controllers\Api\Partner;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Partner\PartnerResource;
use App\Models\Partners\Partner;
use App\Models\Partners\Transporter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
            'type' => ['required'],
            'origin' => ['nullable'],
        ])->validate();

        $partner = Partner::query()->whereHas('transporters', function (Builder $query) {
            $query->where('type', 'like', $this->attributes['type']);
        })
        ->get();


//        ->where('type','=', 'business')
//        ->orWhere('type','=', 'pool')


        return $this->jsonSuccess(PartnerResource::collection($partner));
    }

    public function getDeliveriesQuery(): Builder
    {
        $query = Partner::query();

        if ($this->partner->type === Partner::TYPE_TRANSPORTER) {
            $userable = $this->user->transporters->first();
            $query->where('userable_id', $userable->pivot->id);
        } else {
            $query->where(fn (Builder $builder) => $builder
                ->orWhere('partner_id', $this->partner->id)
                ->orWhere('origin_partner_id', $this->partner->id));

            $this->resolveDeliveriesQueryByRole($query);
        }

        return $query;
    }
}
