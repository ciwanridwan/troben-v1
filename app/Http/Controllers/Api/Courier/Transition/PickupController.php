<?php

namespace App\Http\Controllers\Api\Courier\Transition;

use App\Events\Deliveries\Kurir\Pickup\DriverArrivedAtPickupPoint;
use App\Events\Deliveries\Kurir\Pickup\DriverArrivedAtWarehouse;
use App\Events\Deliveries\Kurir\Pickup\DriverUnloadedPackageInWarehouse;
use App\Events\Deliveries\Kurir\Pickup\PackageLoadedByDriver;
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

    public function loaded(Delivery $delivery): JsonResponse
    {
        event(new PackageLoadedByDriver($delivery));

        return $this->jsonSuccess(DeliveryResource::make($delivery));
    }

    public function finished(Delivery $delivery): JsonResponse
    {
        event(new DriverArrivedAtWarehouse($delivery));

        return $this->jsonSuccess(DeliveryResource::make($delivery));
    }

    public function unloaded(Delivery $delivery): JsonResponse
    {
        event(new DriverUnloadedPackageInWarehouse($delivery));

        return $this->jsonSuccess(DeliveryResource::make($delivery));
    }
}
