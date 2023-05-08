<?php

namespace App\Http\Controllers\Api\Order;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Order\StoreComplaintRequest;
use App\Http\Response;
use App\Models\Packages\Complaint;
use App\Models\Packages\Package;
use Illuminate\Http\JsonResponse;

class ComplaintController extends Controller
{
    /**
     * To create complain from customer
     */
    public function store(StoreComplaintRequest $request): JsonResponse
    {
        $request->validated();

        $package = Package::byHash($request->package_hash);
        if (is_null($package)) {
            return (new Response(Response::RC_BAD_REQUEST, ['message' => 'Hash not valid, please check hash again']));
        }

        $image = null;

        Complaint::create(
            [
                'package_id' => $package->id,
                'type' => $request->type,
                'desc' => $request->desc,
                'photos' => $image
            ]
        );

        return (new Response(Response::RC_SUCCESS))->json();
    }
}
