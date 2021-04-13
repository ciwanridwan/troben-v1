<?php

namespace App\Http\Controllers\Api\Partner\Warehouse\Manifest;

use App\Http\Controllers\Controller;
use App\Jobs\Deliveries\Actions\AssignDriverToDelivery;
use App\Models\Deliveries\Delivery;
use App\Models\Partners\Pivot\UserablePivot;
use Illuminate\Http\JsonResponse;

class AssignationController extends Controller
{
    public function transporter(Delivery $delivery, UserablePivot $userablePivot): JsonResponse
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
