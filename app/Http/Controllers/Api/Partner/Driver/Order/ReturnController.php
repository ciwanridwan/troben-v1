<?php

namespace App\Http\Controllers\Api\Partner\Driver\Order;

use App\Events\Deliveries\Dooring\DriverUnloadedPackageInDooringPoint;
use App\Events\Deliveries\Dooring\PackageLoadedByDriver;
use App\Exceptions\Error;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Delivery\DeliveryResource;
use App\Http\Response;
use App\Jobs\Deliveries\Actions\ProcessFromCodeToDelivery;
use App\Jobs\Packages\DriverUploadReceiver;
use App\Jobs\Packages\UpdateExistingPackage;
use App\Models\Deliveries\Deliverable;
use App\Models\Deliveries\Delivery;
use App\Models\Packages\Package;
use App\Models\Partners\Pivot\UserablePivot;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReturnController extends Controller
{
    /**
     * @param Request $request
     * @param Delivery $delivery
     * @return JsonResponse
     * @throws \Illuminate\Validation\ValidationException
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
        $request->merge([
            'received_at' => Carbon::now(),
        ]);
        $inputs = $request->all();

        /** @noinspection PhpParamsInspection */
        /** @noinspection PhpUnhandledExceptionInspection */
        throw_if(! $package instanceof Package, Error::class, Response::RC_UNAUTHORIZED);

        $job = new UpdateExistingPackage($package, $inputs);
        $this->dispatchNow($job);
        $uploadJob = new DriverUploadReceiver($job->package, $request->file('photos') ?? []);
        $this->dispatchNow($uploadJob);

        event(new DriverUnloadedPackageInDooringPoint($delivery, $package));

        return $this->jsonSuccess(DeliveryResource::make($delivery));
    }
}