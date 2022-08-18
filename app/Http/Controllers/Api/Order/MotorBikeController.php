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
        $handlers = [
            Handling::TYPE_BUBBLE_WRAP,
            Handling::TYPE_WOOD,
            Handling::TYPE_PLASTIC,
            Handling::TYPE_CARDBOARD,
            Handling::TYPE_PALLETE,
            Handling::TYPE_SANDBAG_L,
            Handling::TYPE_SANDBAG_MD,
            Handling::TYPE_SANDBAG_SM,
        ];

        $request->validate([
            'service_code' => 'required|in:tps',
            'transporter_type' => 'required|in:bike,mvp,pickup,engkel box,cde engkel bak,pickup box,cdd double box,fuso bak,cdd double bak,',
            
            'sender_name' => 'required|string',
            'sender_phone' => 'required|string',
            'sender_address' => 'required',

            'receiver_name' => 'required|string',
            'receiver_phone' => 'required|string',
            'receiver_address' => 'required',

            'moto_type' => 'required|in:matic,kopling,gigi',
            'moto_merk' => 'required',
            'moto_cc' => 'required|numeric',
            'moto_year' => 'required|numeric',
            'moto_photo' => 'required|image',
            'moto_price' => 'required|numeric',
            
            'items.*.qty' => 'nullable',
            'items.*.name' => 'nullable',
            'items.*.desc' => 'nullable',
            'items.*.weight' => 'nullable',
            'items.*.height' => 'nullable',
            'items.*.length' => 'nullable',
            'items.*.width' => 'nullable',
            'items.*.insurance' => 'nullable',
            'items.*.is_insured' => 'nullable|boolean',
            'items.*.price' => 'required_if:*.is_insured,true|numeric',
            'items.*.handling.*' => 'nullable:|in:'.implode(',', $handlers),

            'partner_code' => 'required',
            'origin_lat' => 'required',
            'origin_lon' => 'required',
            'destination_lat' => 'required',
            'destination_lon' => 'required',
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
}
