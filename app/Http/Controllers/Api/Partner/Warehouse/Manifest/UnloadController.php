<?php

namespace App\Http\Controllers\Api\Partner\Warehouse\Manifest;

use App\Events\Deliveries\Transit\WarehouseUnloadedPackage;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Delivery\DeliveryResource;
use App\Http\Response;
use App\Models\Deliveries\Delivery;
use Illuminate\Http\Request;

class UnloadController extends Controller
{
    public function unload(Request $request, Delivery $delivery)
    {
        event(new WarehouseUnloadedPackage($delivery, $request->only('code')));

        return (new Response(Response::RC_SUCCESS, DeliveryResource::make($delivery->refresh())))->json();
    }
}
