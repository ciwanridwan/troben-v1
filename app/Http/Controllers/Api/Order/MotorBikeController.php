<?php

namespace App\Http\Controllers\Api\Order;

use App\Actions\Pricing\PricingCalculator;
use App\Casts\Package\Items\Handling;
use App\Http\Controllers\Controller;
use App\Http\Response;
use App\Models\Customers\Customer;
use App\Models\Geo\Regency;
use App\Exceptions\Error;
use App\Http\Resources\Api\Package\PackageResource;
use App\Jobs\Packages\CreateMotorBike;
use App\Jobs\Packages\CreateNewPackage;
use App\Jobs\Packages\CustomerUploadPackagePhotos;
use App\Supports\Geo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MotorBikeController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'service_code' => 'required|in:tps',
            'moto_type' => 'required|in:matic,kopling,gigi',
            
            'sender_name' => 'required|string',
            'sender_phone' => 'required|string',
            'sender_address' => 'required',

            'receiver_name' => 'required|string',
            'receiver_phone' => 'required|string',
            'receiver_address' => 'required',

            'partner_code' => 'required',
            'origin_lat' => 'required|numeric',
            'origin_lon' => 'required|numeric',
            'destination_lat' => 'required|numeric',
            'destination_lon' => 'required|numeric',
        ]);

        $coordOrigin = sprintf('%s,%s', $request->get('origin_lat'), $request->get('origin_lon'));
        $resultOrigin = Geo::getRegional($coordOrigin);
        if ($resultOrigin == null) throw Error::make(Response::RC_INVALID_DATA, ['message' => 'Origin not found', 'coord' => $coordOrigin]);

        $coordDestination = sprintf('%s,%s', $request->get('destination_lat'), $request->get('destination_lon'));
        $resultDestination = Geo::getRegional($coordDestination);
        if ($resultDestination == null) throw Error::make(Response::RC_INVALID_DATA, ['message' => 'Destination not found', 'coord' => $coordDestination]);

        $origin_regency_id = $resultOrigin['regency'];
        $destination_id = $resultDestination['district'];
        $request->merge([
            'origin_regency_id' => $origin_regency_id,
            'destination_id' => $destination_id,
        ]);

        $result = ['result' => 'created'];

        return (new Response(Response::RC_SUCCESS, $result))->json();
    }

    public function storeItem(Request $request): JsonResponse
    {
        $request->validate([
            'moto_type' => 'required|in:matic,kopling,gigi',
            'moto_brand' => 'required',
            'moto_cc' => 'required|numeric',
            'moto_year' => 'required|numeric',
            'moto_photo' => 'required',
            'moto_photo.*' => 'image|max:10240',
            'moto_price' => 'required|numeric',

            'is_insured' => 'required|boolean',
            'height' => 'required_if:*.is_insured,true|numeric',
            'length' => 'required_if:*.is_insured,true|numeric',
            'width' => 'required_if:*.is_insured,true|numeric',
            'price' => 'required_if:*.is_insured,true|numeric',
            'handling.*' => 'required_if:*.is_insured,true|in:'.Handling::TYPE_WOOD,
        ]);

        $result = ['result' => 'inserted'];

        return (new Response(Response::RC_SUCCESS, $result))->json();
    }
}
