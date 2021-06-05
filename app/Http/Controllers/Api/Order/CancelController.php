<?php

namespace App\Http\Controllers\Api\Order;

use App\Events\Packages\PackageCanceledByCustomer;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Package\PackageResource;
use App\Jobs\Packages\SelectCancelPickupMethod;
use App\Models\Packages\Package;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CancelController extends Controller
{
    /**
     * @param \App\Models\Packages\Package $package
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException|\Throwable
     */
    public function cancel(Package $package): JsonResponse
    {
        $this->authorize('update', $package);

        event(new PackageCanceledByCustomer($package));

        return $this->jsonSuccess(PackageResource::make($package->fresh()));
    }

    /**
     * @param \App\Models\Packages\Package $package
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException|\Throwable
     */
    public function method(Request $request, Package $package): JsonResponse
    {
        $this->authorize('update', $package);

        $job = new SelectCancelPickupMethod($package, $request->all());
        $this->dispatch($job);

        return $this->jsonSuccess(PackageResource::make($job->package));
    }
}
