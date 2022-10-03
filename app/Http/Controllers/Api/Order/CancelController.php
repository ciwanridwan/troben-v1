<?php

namespace App\Http\Controllers\Api\Order;

use App\Events\Packages\PackageCanceledByCustomer;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Package\PackageResource;
use App\Http\Response;
use App\Jobs\Packages\SelectCancelPickupMethod;
use App\Models\CancelOrder;
use App\Models\Packages\Package;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CancelController extends Controller
{
    /**
     * @param \App\Models\Packages\Package $package
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException|\Throwable
     */
    // public function cancel(Package $package): JsonResponse
    // {
    //     $this->authorize('update', $package);

    //     event(new PackageCanceledByCustomer($package));

    //     return $this->jsonSuccess(PackageResource::make($package->fresh()));
    // }

    public function cancel(Package $package, Request $request): JsonResponse
    {
        $request->validate([
            'type' => ['required', Rule::in(CancelOrder::getCancelTypes())]
        ]);

        $this->authorize('update', $package);

        event(new PackageCanceledByCustomer($package));

        $data = new CancelOrder();
        $data->package_id = $package->id;
        $data->type = $request->input('type');
        $data->save();

        // return $this->jsonSuccess(PackageResource::make($package->fresh()));
        return (new Response(Response::RC_SUCCESS))->json();
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

    public function cancelBefore(Package $package): JsonResponse
    {
        if ($package->status == 'pending' && $package->payment_status == 'draft' || $package->status == 'created' && $package->payment_status == 'draft') {
            $this->authorize('update', $package);

            event(new PackageCanceledByCustomer($package, ''));

            return $this->jsonSuccess(PackageResource::make($package->fresh()));
        } else {
            return (new Response(Response::RC_DATA_NOT_FOUND))->json();
        }
    }
}
