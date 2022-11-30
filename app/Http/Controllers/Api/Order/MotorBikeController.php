<?php

namespace App\Http\Controllers\Api\Order;

use App\Actions\Pricing\PricingCalculator;
use App\Casts\Package\Items\Handling;
use App\Events\Packages\PackageBikeCreated;
use App\Events\Packages\PackageCreatedForBike;
use App\Http\Controllers\Controller;
use App\Http\Response;
use App\Exceptions\Error;
use App\Exceptions\InvalidDataException;
use App\Jobs\Packages\Actions\AssignFirstPartnerToPackage;
use App\Jobs\Packages\CustomerUploadPackagePhotos;
use App\Models\Packages\Item;
use App\Models\Packages\MotorBike;
use App\Models\Packages\Package;
use App\Models\Partners\Partner;
use App\Models\Partners\Transporter;
use App\Supports\DistanceMatrix;
use App\Supports\Geo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class MotorBikeController extends Controller
{
    public const INSURANCE_MIN = 1000;

    public const INSURANCE_MUL = 0.2 / 100;
    /**
     * Package instance.
     *
     * @var \App\Models\Packages\Package
     */
    public Package $package;

    /**
     * Package attributes.
     *
     * @var array
     */
    protected array $attributes;

    /**
     * Item separation flag.
     *
     * @var bool
     */
    protected bool $isSeparate;

    /**
     * Package items array.
     *
     * @var array
     */
    protected array $items;

    public function store(Request $request): JsonResponse
    {
        $messages = [
            'required' => ':attribute harus diisi'
        ];

        $request->validate([
            'customer_id' => ['nullable', 'exists:customers,id'],
            'service_code' => ['required', 'exists:services,code'],

            'sender_name' => ['required'],
            'sender_phone' => ['required'],
            'sender_address' => ['required'],
            'sender_way_point' => ['nullable'],

            'receiver_name' => ['required'],
            'receiver_phone' => ['required'],
            'receiver_address' => ['required'],
            'receiver_way_point' => ['nullable'],

            /**get from other function with generate, first track in listener App\Listeners\Codes\WriteCodeLog */
            'status' => ['nullable'],
            'payment_status' => ['nullable'],

            /**default false */
            'is_separate_item' => ['nullable', 'boolean'],

            /** Get from PricingCalculator */
            'total_weight' => ['nullable'],
            'tier_price' => ['nullable'],
            'total_amount' => ['nullable'],

            /**From  App\Listeners\Codes\WriteCodeLog, CMIIW*/
            'estimator_id' => ['nullable'],
            'packager_id' => ['nullable'],

            'handling' => ['nullable'],
            'partner_code' => ['nullable', 'exists:partners,code'],

            'origin_lat' => ['required', 'numeric'],
            'origin_lon' => ['required', 'numeric'],
            // 'destination_lat' => ['required', 'numeric'],
            // 'destination_lon' => ['required', 'numeric'],

            /**Validation required for this attributes to get location */
            'origin_regency_id' => ['nullable', 'exists:geo_regencies,id'],
            'destination_regency_id' => ['required', 'exists:geo_regencies,id'],
            'destination_district_id' => ['required', 'exists:geo_districts,id'],
            'destination_sub_district_id' => ['required', 'exists:geo_sub_districts,id'],

            'created_by' => ['nullable', 'exists:customers,id'],
        ], $messages);
        $senderName = [$request->input('sender_name')];
        Log::info('validate package success', $senderName);

        $coordOrigin = sprintf('%s,%s', $request->get('origin_lat'), $request->get('origin_lon'));
        $resultOrigin = Geo::getRegional($coordOrigin, true);

        if ($resultOrigin == null) {
            throw InvalidDataException::make(Response::RC_INVALID_DATA, ['message' => 'Origin not found', 'coord' => $coordOrigin]);
        }

        // $coordDestination = sprintf('%s,%s', $request->get('destination_lat'), $request->get('destination_lon'));
        // $resultDestination = Geo::getRegional($coordDestination, true);

        // if ($resultDestination == null) {
        //     throw Error::make(Response::RC_INVALID_DATA, ['message' => 'Destination not found', 'coord' => $coordDestination]);
        // }

        $origin_regency_id = $resultOrigin['regency'];
        $destination_id = $request->get('destination_sub_district_id');
        $request->merge([
            'origin_regency_id' => $origin_regency_id,
            'destination_id' => $destination_id,
        ]);
        Log::info('check location success', $senderName);

        /**Inserting to tables */
        $data = new Package();
        $data->customer_id = $request->user()->id;
        $data->service_code = $request->input('service_code');
        $data->sender_name = $request->input('sender_name');
        $data->sender_phone = $request->input('sender_phone');
        $data->sender_address = $request->input('sender_address');
        $data->receiver_name = $request->input('receiver_name');
        $data->receiver_phone = $request->input('receiver_phone');
        $data->receiver_address = $request->input('receiver_address');
        $data->is_separate_item = false;
        $data->total_weight = 0;
        $data->tier_price = 0;
        $data->total_amount = 0;

        $data->origin_regency_id = $origin_regency_id;
        $data->destination_regency_id = $request->input('destination_regency_id');
        $data->destination_district_id = $request->input('destination_district_id');
        $data->destination_sub_district_id = $request->input('destination_sub_district_id');
        $data->sender_way_point = $request->input('sender_way_point');
        $data->sender_latitude = $request->input('origin_lat');
        $data->sender_longitude = $request->input('origin_lon');
        $data->receiver_way_point = $request->input('receiver_way_point');
        $data->receiver_latitude = $request->input('destination_lat');
        $data->receiver_longitude = $request->input('destination_lon');
        $data->created_by = $request->user()->first()->id;
        $data->save();
        Log::info('Package have been save, New Order ', $senderName);

        /**Call generate codes by event */
        event(new PackageCreatedForBike($data));
        Log::info('triggering event. ', $senderName);

        $result = ['hash' => $data->hash];

        return (new Response(Response::RC_CREATED, $result))->json();
    }

    public function storeItem(Request $request, Package $package): JsonResponse
    {
        $messages = [
            'price.required_if' => 'attribute harus diisi jika memilih asuransi',
            'height.required' => ':attribute harus diisi jika memilih perlindungan ekstra',
            'length.required' => ':attribute harus diisi jika memilih perlindungan ekstra',
            'width.required' => ':attribute harus diisi jika memilih perlindungan ekstra'
        ];

        $request->validate([
            'moto_type' => 'required|in:matic,kopling,gigi',
            'moto_brand' => 'required',
            'moto_cc' => 'required|numeric',
            'moto_year' => 'required|numeric',
            'moto_photo' => 'required',
            'moto_photo.*' => 'image|max:10240',

            'is_insured' => 'nullable|boolean',
            'price' => 'required_if:is_insured,true|numeric',

            'handling' => 'nullable',
            'handling.*' => 'nullable|in:'.Handling::TYPE_WOOD,
            'height' => [Rule::requiredIf($request->handling != null), 'numeric'],
            'length' => [Rule::requiredIf($request->handling != null), 'numeric'],
            'width' => [Rule::requiredIf($request->handling != null), 'numeric'],

            'transporter_type' => 'required|in:'.Transporter::TYPE_CDD_DOUBLE_BAK.','.Transporter::TYPE_CDD_DOUBLE_BOX.','.Transporter::TYPE_CDE_ENGKEL_BAK.','.Transporter::TYPE_CDE_ENGKEL_BOX.','.Transporter::TYPE_PICKUP_BOX.','.Transporter::TYPE_PICKUP,
            'partner_code' => ['required', 'exists:partners,code']
        ], $messages);

        $package->update(['transporter_type' => $request->input('transporter_type')]);

        $item = new Item();
        $item->package_id = $package->id;
        $item->qty = 1;
        $item->name = $request->input('moto_brand');
        $item->is_insured =  $request->input('is_insured') ?? false;
        $item->price = $request->get('price') ?? 0;
        $item->handling = $request->input('handling.*') ?? null;
        $item->height = $request->input('height') ?? 0;
        $item->length = $request->input('length') ?? 0;
        $item->width = $request->input('width') ?? 0;
        $item->weight = 0;
        $item->in_estimation = true;
        $item->save();

        $data = new MotorBike();
        $data->type = $request->input('moto_type');
        $data->merk = $request->input('moto_brand');
        $data->cc = $request->input('moto_cc');
        $data->years = $request->input('moto_year');
        $data->package_id = $package->id;
        $data->package_item_id = $item->id;
        $data->save();

        $uploadJob = new CustomerUploadPackagePhotos($package, $request->file('moto_photo') ?? []);
        $this->dispatchNow($uploadJob);

        $partner = Partner::where('code', $request->input('partner_code'))->first();
        $transporters = $partner->transporters()->where('type', $request->input('transporter_type'))->first();

        if (is_null($transporters)) {
            $message = ['message' => 'Mitra tidak menyediakan armada yang anda pilih, silahkan pilih type armada yang lain'];

            return (new Response(Response::RC_BAD_REQUEST, $message))->json();
        }

        event(new PackageBikeCreated($package, $partner->code));

        $this->orderAssignation($package, $partner);

        $noReceipt = $package->code()->first()->content;
        $result =
            [
                'receipt' => $noReceipt
            ];

        return (new Response(Response::RC_CREATED, $result))->json();
    }

    public function motorbikeCheck(Request $request): JsonResponse
    {
        $request->validate([
            'origin_lat' => 'required|numeric',
            'origin_lon' => 'required|numeric',
            // 'destination_lat' => 'required|numeric',
            // 'destination_lon' => 'required|numeric',
            'destination_id' => 'nullable|exists:geo_sub_districts,id',

            'moto_type' => 'required|in:matic,kopling,gigi',
            'moto_cc' => 'required|numeric|in:150,250,999',

            /**Handling */
            'handling' => 'nullable|in:'.Handling::TYPE_WOOD,
            'height' => 'required_if:handling,wood|numeric',
            'length' => 'required_if:handling,wood|numeric',
            'width' => 'required_if:handling,wood|numeric',

            /**Pickup Fee */
            'transporter_type' => 'nullable',
            'partner_code' => 'nullable|exists:partners,code',

            /**Insurance Price */
            'price' => 'nullable',
        ]);
        $req = $request->all();

        $coordOrigin = sprintf('%s,%s', $request->get('origin_lat'), $request->get('origin_lon'));
        $resultOrigin = Geo::getRegional($coordOrigin, true);
        if ($resultOrigin == null) {
            throw InvalidDataException::make(Response::RC_INVALID_DATA, ['message' => 'Origin not found', 'coord' => $coordOrigin]);
        }

        // $coordDestination = sprintf('%s,%s', $request->get('destination_lat'), $request->get('destination_lon'));
        // $resultDestination = Geo::getRegional($coordDestination, true);
        // if ($resultDestination == null) {
        //     throw Error::make(Response::RC_INVALID_DATA, ['message' => 'Destination not found', 'coord' => $coordDestination]);
        // }

        $pickup_price = 0;
        if ($request->input('transporter_type') != null && $request->input('transporter_type') != '' && $request->input('partner_code') != '' && $request->input('partner_code') != null) {
            $partner = Partner::where('code', $request->input('partner_code'))->first();
            $origin = $request->input('origin_lat').', '.$request->input('origin_lon');
            $destination = $partner->latitude.', '.$partner->longitude;
            $distance = DistanceMatrix::calculateDistance($origin, $destination);

            if ($request->input('transporter_type') != 'bike') {
                if ($distance < 5) {
                    $pickup_price = 15000;
                } else {
                    $substraction = $distance - 4;
                    $pickup_price = 15000 + (4000 * $substraction);
                }
            }
        }
        $insurance = 0;
        $insurance = ceil(self::getInsurancePrice($request->input('price')));

        $handling_price = 0;
        switch ($req['moto_cc']) {
            case 150:
                $handling_price = 175000;
                break;
            case 250:
                $handling_price = 250000;
                break;
            case 999:
                $handling_price = 450000;
                break;
        }

        $type = $request->get('handling') ?? '';
        $height = $request->get('height');
        $length = $request->get('length');
        $width = $request->get('width');

        $handlingAdditionalPrice = 0;
        // $handlingAdditionalPrice = Handling::calculator($type, $height, $length, $width, 0);
        $handlingAdditionalPrice = self::getHandlingWoodPrice($type, $height, $length, $width);

        $getPrice = PricingCalculator::getBikePrice($resultOrigin['regency'], $req['destination_id']);
        $service_price = 0; // todo get from regional mapping

        switch ($request->get('moto_cc')) {
            case 150:
                $service_price = $getPrice->lower_cc;
                break;
            case 250:
                $service_price = $getPrice->middle_cc;
                break;
            case 999:
                $service_price = $getPrice->high_cc;
                break;
        }
        $total_amount = $pickup_price + $insurance + $handling_price + $handlingAdditionalPrice + $service_price;

        $result = [
            'details' => [
                'pickup_price' => $pickup_price,
                'insurance_price' => $insurance,
                'handling_price' => $handling_price,
                'handling_additional_price' => $handlingAdditionalPrice,
                'service_price' => intval($service_price)
            ],
            'total_amount' => $total_amount,
            'notes' => $getPrice->notes
        ];

        return (new Response(Response::RC_SUCCESS, $result))->json();
    }

    /** Assign firts partner to be partner origin and create delivery */
    private function orderAssignation(Package $package, Partner $partner)
    {
        $job = new AssignFirstPartnerToPackage($package, $partner);
        $this->dispatchNow($job);

        return $job;
    }

    /** Get Insurance Price */
    private static function getInsurancePrice($price)
    {
        return $price > self::INSURANCE_MIN ? $price * self::INSURANCE_MUL : 0;
    }

    private static function getHandlingWoodPrice($type, $height, $length, $width)
    {
        if ($type == '' || $height == 0 || $length == 0 || $width == 0) {
            return 0;
        } else {
            $price = 50000;
            return $price;
        }
    }
}
