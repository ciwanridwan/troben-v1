<?php

namespace App\Http\Controllers\Api\Partner\Warehouse\Manifest;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Deliveries\Delivery;
use App\Http\Controllers\Controller;
use App\Models\Deliveries\Deliverable;
use App\Models\Partners\Pivot\UserablePivot;
use App\Jobs\Deliveries\Actions\AssignDriverToDelivery;
use App\Jobs\Deliveries\Actions\ProcessFromCodeToDelivery;
use App\Models\Code;

class AssignationController extends Controller
{
    public function driver(Delivery $delivery, UserablePivot $userablePivot): JsonResponse
    {
        $job = new AssignDriverToDelivery($delivery, $userablePivot);

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
