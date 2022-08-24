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
use App\Http\Resources\PriceResource;
use App\Jobs\Packages\CreateMotorBike;
use App\Jobs\Packages\CreateNewPackage;
use App\Jobs\Packages\CustomerUploadPackagePhotos;
use App\Models\Partners\Partner;
use App\Models\Price;
use App\Supports\DistanceMatrix;
use App\Supports\Geo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class MotorBikeController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'service_code' => 'required|exists:services,code',

            'sender_name' => 'required',
            'sender_phone' => 'required',
            'sender_address' => 'required',

            'receiver_name' => 'required',
            'receiver_phone' => 'required',
            'receiver_address' => 'required',

            'partner_code' => 'nullable',
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

        $inputs = $request->except('items');

        /**Checkin User as customer */
        $user = $request->user();
        throw_if(!$user instanceof Customer, Error::class, Response::RC_UNAUTHORIZED);
        $inputs['customer_id'] = $user->id;

        /**Check origin regency and destination */
        $regency = Regency::query()->findOrFail($origin_regency_id);
        $payload = array_merge($request->toArray(), ['origin_province_id' => $regency->province_id, 'destination_id' => $destination_id]);

        /**Check Service */
        $tempData = PricingCalculator::calculate($payload, 'array');
        throw_if($tempData['result']['service'] == 0, Error::make(Response::RC_OUT_OF_RANGE));

        /**Job for insert package bike */
        $firstJob = new CreateMotorBike($inputs);

        $result = array($firstJob);

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
            'handling.*' => 'required_if:*.is_insured,true|in:' . Handling::TYPE_WOOD,
        ]);

        // todo handling file upload
        // $photos = [];
        // foreach ((array) $request->file('moto_photo') as $i => $doc) {
        //     $original_filename = $doc->getClientOriginalName();
        //     $filesize = $doc->getSize();
        //     $filename = 'doc_' . md5(microtime(true)) . '.' . $doc->extension();
        //     $doc->move(public_path('uploads/projects-doc'), $filename);
        //     $photos[] = [
        //         'original' => $original_filename,
        //         'size' => $filesize,
        //         'filename' => $filename,
        //     ];
        // }

        $result = ['result' => 'inserted'];

        return (new Response(Response::RC_SUCCESS, $result))->json();
    }

    public function motorbikeCheck(Request $request): JsonResponse
    {
        $request->validate([
            'height' => 'required_if:*.is_insured,true|numeric',
            'length' => 'required_if:*.is_insured,true|numeric',
            'width' => 'required_if:*.is_insured,true|numeric',
        ]);

        // todo handling calculatior wood motorbike

        $rand = mt_rand(10, 99) * 1000;
        $result = ['result' => $rand];

        return (new Response(Response::RC_SUCCESS, $result))->json();
    }

    private static function temporaryCalculate(array $inputs, string $returnType = 'json')
    {
        $inputs =  Validator::validate($inputs, [
            'origin_province_id' => ['required', 'exists:geo_provinces,id'],
            'origin_regency_id' => ['required', 'exists:geo_regencies,id'],
            'destination_id' => ['required', 'exists:geo_sub_districts,id'],
            'partner_code' => ['nullable'],
            'sender_latitude' => ['nullable'],
            'sender_longitude' => ['nullable'],
            'fleet_name' => ['nullable'],
        ]);

        $price = self::getPrice($inputs['origin_province_id'], $inputs['origin_regency_id'], $inputs['destination_id']);
        // $totalWeightBorne = self::getTotalWeightBorne($inputs['items']);
        $insurancePriceTotal = 0;
        $pickup_price = 0;

        if (array_key_exists('fleet_name', $inputs) && $inputs['partner_code'] != '' && $inputs['partner_code'] != null) {
            $partner = Partner::where('code', $inputs['partner_code'])->first();
            $origin = $inputs['sender_latitude'].', '.$inputs['sender_longitude'];
            $destination = $partner->latitude.', '.$partner->longitude;
            $distance = DistanceMatrix::calculateDistance($origin, $destination);

            if ($inputs['fleet_name'] == 'bike') {
                if ($distance < 5) {
                    $pickup_price = 8000;
                } else {
                    $substraction = $distance - 4;
                    $pickup_price = 8000 + (2000 * $substraction);
                }
            } else {
                if ($distance < 5) {
                    $pickup_price = 15000;
                } else {
                    $substraction = $distance - 4;
                    $pickup_price = 15000 + (4000 * $substraction);
                }
            }
        }

        $discount = 0;
        $handling_price = 0;

        $tierPrice = self::getTier($price, $totalWeightBorne);
        $servicePrice = self::getServicePrice($inputs, $price);

        $response = [
            'price' => PriceResource::make($price),
            'result' => [
                'insurance_price_total' => $insurancePriceTotal,
                'total_weight_borne' => $totalWeightBorne,
                'handling' => $handling_price,
                'pickup_price' => $pickup_price,
                'discount' => $discount,
                'tier' => $tierPrice,
                'service' => $servicePrice
            ]
        ];

        switch ($returnType) {
            case 'array':
                return $response;
            case 'json':
                return (new Response(Response::RC_SUCCESS, $response))->json();
                break;
            default:
                return (new Response(Response::RC_SUCCESS, $response))->json();
                break;
        }
    }


    private static function getPrice()
    {

    }

    private static function getTotalWeightBorne()
    {

    }

    public static function getServicePrice(array $inputs, ?Price $price = null)
    {
        $inputs =  Validator::validate($inputs, [
            'origin_province_id' => [Rule::requiredIf(! $price), 'exists:geo_provinces,id'],
            'origin_regency_id' => [Rule::requiredIf(! $price), 'exists:geo_regencies,id'],
            'destination_id' => [Rule::requiredIf(! $price), 'exists:geo_sub_districts,id'],
            'items' => ['required'],
            'items.*.height' => ['required', 'numeric'],
            'items.*.length' => ['required', 'numeric'],
            'items.*.width' => ['required', 'numeric'],
            'items.*.weight' => ['required', 'numeric'],
            'items.*.qty' => ['required', 'numeric'],
            'items.*.handling' => ['nullable']
        ]);

        if (! $price) {
            /** @var Price $price */
            $price = self::getPrice($inputs['origin_province_id'], $inputs['origin_regency_id'], $inputs['destination_id']);
        }

        $items = [];
        foreach ($inputs['items'] as $item) {
            if ($item['handling']) {
                foreach ($item['handling'] as $handling) {
                    $packing[] = [
                        'type' => $handling['type']
                    ];
                }
            }
            $items[] = [
                'weight' => $item['weight'],
                'height' => $item['height'],
                'length' => $item['length'],
                'width' => $item['width'],
                'qty' => $item['qty'],
                'handling' => ! empty($packing) ? array_column($packing, 'type') : null

            ];
        }
        $totalWeightBorne = self::getTotalWeightBorne($items);

        $tierPrice = self::getTier($price, $totalWeightBorne);

        $servicePrice = $tierPrice * $totalWeightBorne;
        return $servicePrice;
    }
}
