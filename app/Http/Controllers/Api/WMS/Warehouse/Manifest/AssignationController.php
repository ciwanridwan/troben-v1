<?php

namespace App\Http\Controllers\Api\WMS\Warehouse\Manifest;

use App\Jobs\Deliveries\Actions\RequestPartnerToDelivery;
use App\Models\Partners\Partner;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Deliveries\Delivery;
use App\Http\Controllers\Controller;
use App\Models\Deliveries\Deliverable;
use App\Models\Partners\Pivot\UserablePivot;
use App\Jobs\Deliveries\Actions\AssignDriverToDelivery;
use App\Jobs\Deliveries\Actions\AssignPartnerToDelivery;
use App\Jobs\Deliveries\Actions\ProcessFromCodeToDelivery;

/**
 * Class AssignationController.
 */
class AssignationController extends Controller
{
    /**
     * @param Delivery $delivery
     * @param UserablePivot $userablePivot
     * @return JsonResponse
     */
    public function driver(Delivery $delivery, UserablePivot $userablePivot): JsonResponse
    {
        $method = 'assign_driver';
        $job = new AssignDriverToDelivery($delivery, $userablePivot, $method);

        $this->dispatchNow($job);

        return $this->jsonSuccess();
    }

    /**
     * @param Delivery $delivery
     * @return JsonResponse
     */
    public function requestPartner(Delivery $delivery): JsonResponse
    {
        $job = new RequestPartnerToDelivery($delivery);

        $this->dispatchNow($job);

        return $this->jsonSuccess();
    }

}
