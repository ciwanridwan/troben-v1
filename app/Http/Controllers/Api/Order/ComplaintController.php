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
            return (new Response(Response::RC_BAD_REQUEST, ['message' => 'Hash not valid, please check hash again']))->json();
        }

        if (!is_null($package->complaints) || $package->complaints()->exists()) {
            return (new Response(Response::RC_BAD_REQUEST, ['message' => 'Customer has submit complaint, cant submit complaint']))->json();
        }

        $imageArr = [];
        if ($request->photos) {
            foreach ($request->photos as $picture) {
                $images = handleUpload($picture, 'pack_customer_complaint');

                array_push($imageArr, $images);
            }
        }

        $imageToDb = ["photos" => $imageArr];

        Complaint::create(
            [
                'package_id' => $package->id,
                'type' => $request->type,
                'desc' => $request->desc,
                'meta' => json_encode($imageToDb)
            ]
        );

        return (new Response(Response::RC_SUCCESS))->json();
    }
}
