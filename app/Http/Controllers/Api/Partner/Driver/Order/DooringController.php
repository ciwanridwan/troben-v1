<?php

namespace App\Http\Controllers\Api\Partner\Driver\Order;

use App\Events\Deliveries\Dooring\DriverArrivedAtDooringPoint;
use App\Events\Deliveries\Dooring\DriverArrivedAtOriginPartner;
use App\Events\Deliveries\Dooring\DriverUnloadedPackageInDooringPoint;
use App\Events\Deliveries\Dooring\PackageLoadedByDriver;
use App\Exceptions\Error;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Delivery\DeliveryResource;
use App\Http\Response;
use App\Jobs\Deliveries\Actions\ProcessFromCodeToDelivery;
use App\Jobs\Packages\CheckDeliveredStatus;
use App\Jobs\Packages\DriverUploadReceiver;
use App\Jobs\Packages\UpdateExistingPackage;
use App\Models\Deliveries\Deliverable;
use App\Models\Deliveries\Delivery;
use App\Models\Packages\Package;
use App\Models\Partners\Pivot\UserablePivot;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

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
     * @param Package $package
     * @param Request $request
     * @return JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Throwable
     */
    public function unloaded(Delivery $delivery, Package $package, Request $request): JsonResponse
    {
        $request->validate([
            'received_by' => 'required',
            'photos' => 'required',
            'photos.*' => 'required', 'image'
        ]);
        $inputs = array_merge($request->only(['received_by', 'photos']),[
            'received_at' => Carbon::now(),
            'status' => Package::STATUS_DELIVERED,
        ]);

        /** @noinspection PhpParamsInspection */
        /** @noinspection PhpUnhandledExceptionInspection */
        throw_if(! $package instanceof Package, Error::class, Response::RC_UNAUTHORIZED);

        $job = new UpdateExistingPackage($package, Arr::only($inputs,['received_by','received_at','status']));
        $this->dispatchNow($job);
        $uploadJob = new DriverUploadReceiver($job->package, $request->file('photos') ?? []);
        $this->dispatchNow($uploadJob);

        event(new DriverUnloadedPackageInDooringPoint($delivery, $package));

        $job = new CheckDeliveredStatus($delivery);
        $this->dispatchNow($job);

        return $this->jsonSuccess(DeliveryResource::make($delivery));
    }


    public function show(Delivery $delivery): JsonResponse
    {
        return $this->jsonSuccess(DeliveryResource::make($delivery->load(
            'code',
            'partner',
            'packages',
            'packages.origin_district',
            'packages.origin_sub_district',
            'packages.destination_sub_district',
            'packages.items',
            'driver',
            'transporter',
            'item_codes.codeable'
        )));
    }
}
