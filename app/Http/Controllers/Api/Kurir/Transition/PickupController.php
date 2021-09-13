<?php

namespace App\Http\Controllers\Api\Kurir\Transition;

use App\Events\Deliveries\Kurir\Pickup\CustomerPackageApproved;
use App\Events\Deliveries\Kurir\Pickup\WaitingCustomerConfirmation;
use App\Events\Deliveries\Pickup\DriverArrivedAtPickupPoint;
use App\Events\Deliveries\Pickup\DriverArrivedAtWarehouse;
use App\Events\Deliveries\Pickup\DriverUnloadedPackageInWarehouse;
use App\Events\Deliveries\Pickup\PackageLoadedByDriver;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Delivery\DeliveryResource;
use App\Models\Deliveries\Delivery;
use Illuminate\Http\JsonResponse;

class PickupController extends Controller
{
    public function arrived(Delivery $delivery): JsonResponse
    {
        event(new DriverArrivedAtPickupPoint($delivery));

        return $this->jsonSuccess(DeliveryResource::make($delivery));
    }

    public function confirmationProcess(Delivery $delivery): JsonResponse
    {
        event(new WaitingCustomerConfirmation($delivery));

        return $this->jsonSuccess(DeliveryResource::make($delivery));
    }

    public function customerApproved(Delivery $delivery): JsonResponse
    {
        event(new CustomerPackageApproved($delivery));

        return $this->jsonSuccess(DeliveryResource::make($delivery));
    }

    public function finished(Delivery $delivery): JsonResponse
    {
        event(new DriverArrivedAtWarehouse($delivery));

        return $this->jsonSuccess(DeliveryResource::make($delivery));
    }
}
