<?php

namespace App\Http\Controllers\Api\Partner\Warehouse\Manifest;

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
 * Class AssignationController
 * @package App\Http\Controllers\Api\Partner\Warehouse\Manifest
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
        $job = new AssignDriverToDelivery($delivery, $userablePivot);

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

    /**
     * @param Delivery $delivery
     * @param Partner $partner
     * @return JsonResponse
     */
    public function partner(Delivery $delivery, Partner $partner): JsonResponse
    {
        $job = new AssignPartnerToDelivery($delivery, $partner);

        $this->dispatchNow($job);

        return $this->jsonSuccess();
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Deliveries\Delivery $delivery
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function package(Request $request, Delivery $delivery): JsonResponse
    {
        $job = new ProcessFromCodeToDelivery($delivery, array_merge($request->only(['code']), [
            'status' => Deliverable::STATUS_PREPARED_BY_ORIGIN_WAREHOUSE,
            'role' => UserablePivot::ROLE_WAREHOUSE
        ]));

        $this->dispatchNow($job);

        return $this->jsonSuccess();
    }
}
