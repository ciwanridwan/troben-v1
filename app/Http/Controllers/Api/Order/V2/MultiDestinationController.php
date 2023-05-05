<?php

namespace App\Http\Controllers\Api\Order\V2;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Order\StoreMultiDestinationRequest;
use App\Http\Response;
use Illuminate\Http\JsonResponse;

class MultiDestinationController extends Controller
{
    /**
     * To create new order multi destination
     * on customer apps
     */
    public function store(StoreMultiDestinationRequest $request): JsonResponse
    {
        $request->validated();
        return (new Response(Response::RC_SUCCESS))->json();
    }
}
