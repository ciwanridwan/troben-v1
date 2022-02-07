<?php

namespace App\Http\Controllers\Api\Partner\Driver\Order;

use App\Events\Deliveries\Transit\DriverArrivedAtDestinationWarehouse;
use App\Events\Deliveries\Transit\DriverArrivedAtOriginWarehouse;
use App\Events\Deliveries\Transit\DriverUnloadedPackageInDestinationWarehouse;
use App\Events\Deliveries\Transit\PackageLoadedByDriver;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Delivery\DeliveryResource;
use App\Http\Response;
use App\Jobs\Deliveries\Actions\ProcessFromCode;
use App\Jobs\Deliveries\Actions\ProcessFromCodeToDelivery;
use App\Models\Code;
use App\Models\CodeLogable;
use App\Models\Deliveries\Deliverable;
use App\Models\Deliveries\Delivery;
use App\Supports\Repositories\PartnerRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransitController extends Controller
{
    public function arrived(Delivery $delivery): JsonResponse
    {
        event(new DriverArrivedAtOriginWarehouse($delivery));

        return $this->jsonSuccess(DeliveryResource::make($delivery->refresh()));
    }

    /**
     * Driver loading package
     * Route Path       : {API_DOMAIN}/partner/driver/order/transit/{delivery_hash}/loaded
     * Route Name       : api.partner.driver.order.transit.loaded
     * Route Method     : PATCH.
     *
     * @param Request $request
     * @param Delivery $delivery
     * @return JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function loaded(Request $request, Delivery $delivery): JsonResponse
    {
        $job = new ProcessFromCodeToDelivery($delivery, array_merge($request->only(['code']), [
            'status' => Deliverable::STATUS_LOAD_BY_DRIVER,
            'role' => CodeLogable::STATUS_DRIVER_LOAD
        ]));

        $this->dispatchNow($job);

        event(new PackageLoadedByDriver($delivery));

        return $this->jsonSuccess(DeliveryResource::make($delivery));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function loadedItems(Request $request, PartnerRepository $repository): JsonResponse
    {
        $job = new ProcessFromCode(array_merge($request->only(['codes']), [
            'status' => Deliverable::STATUS_LOAD_BY_DRIVER,
            'role' => CodeLogable::STATUS_DRIVER_LOAD
        ]));

        $this->dispatchNow($job);
        if ($job->status == 'fail'){
            return (new Response(Response::RC_BAD_REQUEST))->json();
        }
        $items = Code::select('id')
            ->whereIn('content', $request->codes)
            ->pluck('id')->toArray();
        $deliveries = Deliverable::select('delivery_id')
            ->where('deliverable_type', 'App\Models\Code')
            ->where('status', 'load_by_driver')
            ->whereHas('delivery', function($q) use ($repository) {
                $q->where('partner_id', $repository->getPartner()->id);
            })
            ->whereIn('deliverable_id', $items)
            ->pluck('delivery_id')->toArray();

        foreach ($deliveries as $id){
            $delivery = Delivery::find($id);
            event(new PackageLoadedByDriver($delivery));
        }
        return (new Response(Response::RC_SUCCESS))->json();
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
