<?php

namespace App\Http\Controllers\Api\Partner;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Partner\PartnerResource;
use App\Models\Partners\Partner;
use App\Models\Partners\Transporter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
<<<<<<< HEAD
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
=======
>>>>>>> 16cf071746731e426e98d1052b699c7a1e3d3185

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

        $partner = Partner::query()
        ->where('type','=', 'business')
        ->orWhere('type','=', 'pool')
        ->whereHas('transporters', function (Builder $query) {
            $query->where('type', 'like', $this->attributes['type']);
        })
        ->get();

        return $this->jsonSuccess(PartnerResource::collection($partner));
    }
}
