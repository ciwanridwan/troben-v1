<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\Partners\Transporter;
use App\Http\Resources\Api\Transporter\AvailableTransporterResource;
use App\Models\Service;

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
        $request->validate(
            [
                'service_code' => ['nullable', 'exists:services,code'],
                'type' => ['nullable', 'in:bike,other'],
                'details' => ['nullable', 'boolean']
            ]
        );

        switch ($request->type) {
            case 'bike':
                if ($request->details) {
                    return $this->jsonSuccess(AvailableTransporterResource::collection(Transporter::getDetailTransporterOfBike()));
                }
                return $this->jsonSuccess(AvailableTransporterResource::collection(Transporter::getTranporterOfBike()));
                break;
            case 'other':
            default:
                if ($request->details) {
                    switch ($request->service_code) {
                        case Service::TRAWLPACK_CUBIC:
                            return $this->jsonSuccess(AvailableTransporterResource::collection(Transporter::getDetailCubicTypes()));
                            break;
                        case Service::TRAWLPACK_EXPRESS:
                            return $this->jsonSuccess(AvailableTransporterResource::collection(Transporter::getDetailCubicTypes()));
                            break;
                        case Service::TRAWLPACK_STANDARD:
                        default:
                            return $this->jsonSuccess(AvailableTransporterResource::collection(Transporter::getDetailAvailableTypes()));
                            break;
                    }
                }

                switch ($request->service_code) {
                    case Service::TRAWLPACK_CUBIC:
                        return $this->jsonSuccess(AvailableTransporterResource::collection(Transporter::getAvailableCubicTypes()));
                        break;
                    case Service::TRAWLPACK_EXPRESS:
                        return $this->jsonSuccess(AvailableTransporterResource::collection(Transporter::getAvailableCubicTypes()));
                        break;
                    case Service::TRAWLPACK_STANDARD:
                    default:
                        return $this->jsonSuccess(AvailableTransporterResource::collection(Transporter::getAvailableTypes()));
                        break;
                }
                break;
        }
    }
}
