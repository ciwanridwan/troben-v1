<?php

namespace App\Http\Controllers\Api\Partner\Driver\Order;

use Illuminate\Http\JsonResponse;
use App\Models\Deliveries\Delivery;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Delivery\DeliveryResource;
use App\Events\Deliveries\Pickup\PackageLoadedByDriver;
use App\Events\Deliveries\Pickup\DriverArrivedAtWarehouse;
use App\Events\Deliveries\Pickup\DriverArrivedAtPickupPoint;
use App\Events\Deliveries\Pickup\DriverUnloadedPackageInWarehouse;
use App\Events\Packages\PackageCanceledByDriver;
use App\Models\User;
use App\Services\Chatbox\Chatbox;

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

    public function cancel(Delivery $delivery): JsonResponse
    {
        event(new PackageCanceledByDriver($delivery));

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
        $driverSignIn = User::where('id', $delivery->driver->id)->first();
        if ($driverSignIn) {
            $token = auth('api')->login($driverSignIn);
        }
        $param = [
            'token' => $token ?? null,
            'type' => 'trawlpack',
            'participant_id' => $delivery->driver->id,
            'customer_id' => $delivery->packages[0]->customer_id
        ];
        Chatbox::endSessionDriverChatbox($param);
        return $this->jsonSuccess(DeliveryResource::make($delivery));
    }
}
