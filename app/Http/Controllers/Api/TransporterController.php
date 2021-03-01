<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\Partners\Transporter;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\Api\Transporter\AvailableTransporterResource;

class TransporterController extends Controller
{
    /**
     * Filtered attributes.
     *
     * @var array
     */
    protected $attributes;

    /**
     * Get Type of Transporter List
     * Route Path       : {API_DOMAIN}/transporter
     * Route Name       : api.transporter
     * Route Method     : GET.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function list(Request $request): JsonResponse
    {
        $this->attributes = Validator::make($request->all(), [
            'details' => ['nullable','boolean'],
        ])->validate();

        if ($request->details) {
            return $this->jsonSuccess(AvailableTransporterResource::collection(Transporter::getDetailAvailableTypes()));
        }

        return $this->jsonSuccess(AvailableTransporterResource::collection(Transporter::getAvailableTypes()));
    }
}
