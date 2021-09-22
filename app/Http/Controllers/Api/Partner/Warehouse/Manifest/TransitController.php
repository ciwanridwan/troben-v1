<?php

namespace App\Http\Controllers\Api\Partner\Warehouse\Manifest;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Delivery\DeliveryResource;
use App\Http\Response;
use App\Jobs\Deliveries\Actions\UnloadCodeFromDelivery;
use App\Models\Deliveries\Deliverable;
use App\Models\Deliveries\Delivery;
use App\Models\Partners\Pivot\UserablePivot;
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
            'role' => UserablePivot::ROLE_WAREHOUSE,
        ]));

        $this->dispatch($job);

        $delivery->refresh();

        return (new Response(Response::RC_SUCCESS, DeliveryResource::make($delivery->load('packages'))))->json();
    }
}
