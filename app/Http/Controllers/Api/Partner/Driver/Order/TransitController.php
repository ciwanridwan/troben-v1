<?php

namespace App\Http\Controllers\Api\Partner\Driver\Order;

use App\Events\Deliveries\Transit\DriverArrivedAtDestinationWarehouse;
use App\Events\Deliveries\Transit\DriverArrivedAtOriginWarehouse;
use App\Events\Deliveries\Transit\DriverUnloadedPackageInDestinationWarehouse;
use App\Events\Deliveries\Transit\PackageLoadedByDriver;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Delivery\DeliveryResource;
use App\Jobs\Deliveries\Actions\ProcessFromCodeToDelivery;
use App\Models\Deliveries\Deliverable;
use App\Models\Deliveries\Delivery;
use App\Models\Partners\Pivot\UserablePivot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransitController extends Controller
{
    public function arrived(Delivery $delivery): JsonResponse
    {
        event(new DriverArrivedAtOriginWarehouse($delivery));

        return $this->jsonSuccess(DeliveryResource::make($delivery->refresh()));
    }

    public function loaded(Request $request, Delivery $delivery): JsonResponse
    {
        $job = new ProcessFromCodeToDelivery($delivery, array_merge($request->only(['code']), [
            'status' => Deliverable::STATUS_LOAD_BY_DRIVER,
            'role' => UserablePivot::ROLE_DRIVER
        ]));

        $this->dispatchNow($job);

        event(new PackageLoadedByDriver($delivery));

        return $this->jsonSuccess(DeliveryResource::make($delivery));
    }

    public function finished(Delivery $delivery): JsonResponse
    {
        event(new DriverArrivedAtDestinationWarehouse($delivery));

        return $this->jsonSuccess(DeliveryResource::make($delivery));
    }

    public function unloaded(Delivery $delivery): JsonResponse
    {
        event(new DriverUnloadedPackageInDestinationWarehouse($delivery));

        return $this->jsonSuccess(DeliveryResource::make($delivery));
    }
}
