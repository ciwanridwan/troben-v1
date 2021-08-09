<?php

namespace App\Http\Controllers\Api\Partner;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Partner\PartnerResource;
use App\Models\Partners\Partner;
use App\Models\Partners\Transporter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
    public function list(): JsonResponse
    {
        $partner = Partner::query()->where('type', 'business')
            ->orWhere('type', 'business')
            ->get();

        return $this->jsonSuccess(PartnerResource::collection($partner));
    }
}
