<?php

namespace App\Http\Controllers\Api\Partner\Warehouse\Manifest;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Delivery\DeliveryResource;
use App\Http\Response;
use App\Jobs\Deliveries\Actions\UnloadCode;
use App\Jobs\Deliveries\Actions\UnloadCodeFromDelivery;
use App\Models\CodeLogable;
use App\Models\Deliveries\Deliverable;
use App\Models\Deliveries\Delivery;
use Illuminate\Http\Request;

class TransitController extends Controller
{
    /**
     * @param Request $request
     * @param Delivery $delivery
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function unload(Request $request, Delivery $delivery)
    {
        $job = new UnloadCodeFromDelivery($delivery, array_merge($request->only('code'), [
            'status' => Deliverable::STATUS_UNLOAD_BY_DESTINATION_WAREHOUSE,
            'role' => CodeLogable::STATUS_WAREHOUSE_UNLOAD,
        ]));

        $this->dispatch($job);

        $delivery->refresh();

        return (new Response(Response::RC_SUCCESS, DeliveryResource::make($delivery->load('packages'))))->json();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function unloadItem(Request $request)
    {
        $job = new UnloadCode(array_merge($request->only('code'), [
            'status' => Deliverable::STATUS_UNLOAD_BY_DESTINATION_WAREHOUSE,
            'role' => CodeLogable::STATUS_WAREHOUSE_UNLOAD,
        ]));
        $this->dispatch($job);
        if ($job->status == 'fail'){
            return (new Response(Response::RC_BAD_REQUEST))->json();
        }

        return (new Response(Response::RC_SUCCESS))->json();
    }
}
