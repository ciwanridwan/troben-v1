<?php

namespace App\Http\Controllers\Api\Partner\Warehouse\Manifest;

use Illuminate\Http\JsonResponse;
use App\Models\Deliveries\Delivery;
use App\Http\Controllers\Controller;
use App\Models\Partners\Pivot\UserablePivot;
use App\Jobs\Deliveries\Actions\AssignDriverToDelivery;

class AssignationController extends Controller
{
    public function driver(Delivery $delivery, UserablePivot $userablePivot): JsonResponse
    {
        $job = new AssignDriverToDelivery($delivery, $userablePivot);

        $this->dispatchNow($job);

        return $this->jsonSuccess();
    }

    public function package(): JsonResponse
    {
        return $this->coming();
    }
}
