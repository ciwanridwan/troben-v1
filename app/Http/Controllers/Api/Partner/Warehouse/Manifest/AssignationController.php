<?php

namespace App\Http\Controllers\Api\Partner\Warehouse\Manifest;

use App\Jobs\Deliveries\Actions\AttachPackageToDelivery;
use Illuminate\Http\JsonResponse;
use App\Models\Deliveries\Delivery;
use App\Http\Controllers\Controller;
use App\Models\Partners\Pivot\UserablePivot;
use App\Jobs\Deliveries\Actions\AssignDriverToDelivery;
use Illuminate\Http\Request;

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
        $job = new AttachPackageToDelivery($delivery, $request->all());

        $this->dispatchNow($job);

        return $this->jsonSuccess();
    }
}
