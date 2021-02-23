<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Transporter\AvailableTransporterResource;
use App\Models\Partners\Transporter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
    public function list(Request $request)
    {
        $this->attributes = Validator::make($request->all(),[
            'details' => ['nullable','boolean'],
        ])->validate();

        if ($request->details) {
            return $this->jsonSuccess(AvailableTransporterResource::collection(Transporter::getDetailAvailableTypes()));
        }

        return $this->jsonSuccess(AvailableTransporterResource::collection(Transporter::getAvailableTypes()));
    }
}
