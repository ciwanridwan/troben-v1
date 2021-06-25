<?php

namespace App\Http\Controllers\Api\Partner\Driver\Order;

use App\Events\Deliveries\Dooring\DriverArrivedAtDooringPoint;
use App\Events\Deliveries\Dooring\DriverArrivedAtOriginPartner;
use App\Events\Deliveries\Dooring\DriverUnloadedPackageInDooringPoint;
use App\Events\Deliveries\Dooring\PackageLoadedByDriver;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Delivery\DeliveryResource;
use App\Jobs\Deliveries\Actions\ProcessFromCodeToDelivery;
use App\Models\Code;
use App\Models\Deliveries\Deliverable;
use App\Models\Deliveries\Delivery;
use App\Models\Packages\Package;
use App\Models\Partners\Pivot\UserablePivot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class DooringController extends Controller
{
    /**
     * @param Delivery $delivery
     * @return JsonResponse
     */
    public function arrived(Delivery $delivery): JsonResponse
    {
        event(new DriverArrivedAtOriginPartner($delivery));

        return $this->jsonSuccess(DeliveryResource::make($delivery));
    }

    /**
     * @param Request $request
     * @param Delivery $delivery
     * @return JsonResponse
     */
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

    /**
     * @param Delivery $delivery
     * @return JsonResponse
     */
    public function finished(Delivery $delivery): JsonResponse
    {
        event(new DriverArrivedAtDooringPoint($delivery));

        return $this->jsonSuccess(DeliveryResource::make($delivery));
    }

    /**
     * @param Delivery $delivery
     * @return JsonResponse
     */
    public function unloaded(Delivery $delivery, $package): JsonResponse
    {
        $package = Code::query()->where('content', $package)->first()->codeable;

        throw_if(! $package instanceof Package, ValidationException::withMessages([
            'error' => __('must be package.'),
        ]));

        event(new DriverUnloadedPackageInDooringPoint($delivery, $package));

        return $this->jsonSuccess(DeliveryResource::make($delivery));
    }
}
