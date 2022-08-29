<?php

namespace App\Http\Controllers\Api\Order;

use App\Actions\Pricing\PricingCalculator;
use App\Casts\Package\Items\Handling;
use App\Events\Packages\PackageBikeCreated;
use App\Events\Packages\PackageCreatedForBike;
use App\Http\Controllers\Controller;
use App\Http\Response;
use App\Exceptions\Error;
use App\Jobs\Packages\CustomerUploadPackagePhotos;
use App\Models\Packages\Item;
use App\Models\Packages\MotorBike;
use App\Models\Packages\Package;
use App\Models\Partners\Transporter;
use App\Supports\Geo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MotorBikeController extends Controller
{
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
        $request->validate([
            'customer_id' => ['nullable', 'exists:customers,id'],
            'service_code' => ['required', 'exists:services,code'],

            'sender_name' => ['required'],
            'sender_phone' => ['required'],
            'sender_address' => ['required'],

            'receiver_name' => ['required'],
            'receiver_phone' => ['required'],
            'receiver_address' => ['required'],

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
            'destination_lat' => ['required', 'numeric'],
            'destination_lon' => ['required', 'numeric'],

            /**Validation required for this attributes to get location */
            'origin_regency_id' => ['nullable', 'exists:geo_regencies,id'],
            'destination_regency_id' => ['nullable', 'exists:geo_regencies,id'],
            'destination_district_id' => ['nullable', 'exists:geo_districts,id'],
            'destination_sub_district' => ['nullable', 'exists:geo_sub_districts,id'],

            'created_by' => ['nullable', 'exists:customers,id'],
        ]);
        $senderName = array($request->input('sender_name'));
        Log::info('validate package success', $senderName);

        $coordOrigin = sprintf('%s,%s', $request->get('origin_lat'), $request->get('origin_lon'));
        $resultOrigin = Geo::getRegional($coordOrigin, true);

        if ($resultOrigin == null) throw Error::make(Response::RC_INVALID_DATA, ['message' => 'Origin not found', 'coord' => $coordOrigin]);

        $coordDestination = sprintf('%s,%s', $request->get('destination_lat'), $request->get('destination_lon'));
        $resultDestination = Geo::getRegional($coordDestination, true);

        if ($resultDestination == null) throw Error::make(Response::RC_INVALID_DATA, ['message' => 'Destination not found', 'coord' => $coordDestination]);

        $origin_regency_id = $resultOrigin['regency'];
        $destination_id = $resultDestination['district'];
        $request->merge([
            'origin_regency_id' => $origin_regency_id,
            'destination_id' => $destination_id,
        ]);


        /**Inserting to tables */
        $data = new Package();
        $data->customer_id = $request->user()->first()->id;
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
        $data->destination_regency_id = $resultDestination['regency'];
        $data->destination_district_id = $destination_id;
        $data->destination_sub_district_id = $resultDestination['subdistrict'];
        $data->sender_way_point = $request->input('sender_address');
        $data->sender_latitude = $request->input('origin_lat');
        $data->sender_longitude = $request->input('origin_lon');
        $data->created_by = $request->user()->first()->id;
        $data->save();

        /**Call generate codes by event */
        event(new PackageCreatedForBike($data));
        Log::info('triggering event. ', $senderName);

        $result = ['hash' => $data->hash];

        return (new Response(Response::RC_CREATED, $result))->json();
    }

    public function storeItem(Request $request, Package $package): JsonResponse
    {
        $request->validate([
            'moto_type' => 'required|in:matic,kopling,gigi',
            'moto_brand' => 'required',
            'moto_cc' => 'required|numeric',
            'moto_year' => 'required|numeric',
            'moto_photo' => 'required',
            'moto_photo.*' => 'image|max:10240',
            
            'is_insured' => 'nullable|boolean',
            'price' => 'required_if:is_insured,true',
            'handling' => 'nullable',
            'handling.*' => 'nullable|in:' . Handling::TYPE_WOOD,
            'height' => 'required_if:handling,wood|numeric',
            'length' => 'required_if:handling,wood|numeric',
            'width' => 'required_if:handling,wood|numeric',

            'transporter_type' => 'required|in:' . Transporter::TYPE_MPV,
            'partner_code' => ['required', 'exists:partners,code']
        ]);

        $package->update(['transporter_type' => $request->input('transporter_type')]);

        $item = new Item();
        $item->package_id = $package->id;
        $item->qty = 1;
        $item->name = $request->input('moto_brand');
        $item->is_insured =  $request->input('is_insured') ?? false;
        $item->price = $request->input('price') ?? 0;
        $item->handling = $request->input('handling');
        $item->height = $request->input('height') ?? 0;
        $item->length = $request->input('length') ?? 0;
        $item->width = $request->input('width') ?? 0;
        if (is_null($item->price) && is_null($item->handling) && is_null($item->height) && is_null($item->length) && is_null($item->width)) {
            $item->handling = null;
            $item->price = $item->height = $item->length = $item->width = 0;
        }
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

        $partnerCode = $request->input('partner_code');

        event(new PackageBikeCreated($package, $partnerCode));

        $result = ['hash' => $package->hash];

        return (new Response(Response::RC_CREATED, $result))->json();
    }

    public function motorbikeCheck(Request $request): JsonResponse
    {
        $request->validate([
            'origin_lat' => 'required|numeric',
            'origin_lon' => 'required|numeric',
            'destination_lat' => 'required|numeric',
            'destination_lon' => 'required|numeric',

            'moto_type' => 'required|in:matic,kopling,gigi',
            'moto_brand' => 'required',
            'moto_cc' => 'required|numeric|in:150,250,999',
            'moto_price' => 'required|numeric',

            'is_insured' => 'required|boolean',
            'height' => 'required_if:*.is_insured,true|numeric',
            'length' => 'required_if:*.is_insured,true|numeric',
            'width' => 'required_if:*.is_insured,true|numeric',
            // 'price' => 'required_if:*.is_insured,true|numeric', // disable
            'handling.*' => 'required_if:*.is_insured,true|in:' . Handling::TYPE_WOOD,
        ]);

        $req = $request->all();

        $coordOrigin = sprintf('%s,%s', $request->get('origin_lat'), $request->get('origin_lon'));
        $resultOrigin = Geo::getRegional($coordOrigin, true);
        if ($resultOrigin == null) throw Error::make(Response::RC_INVALID_DATA, ['message' => 'Origin not found', 'coord' => $coordOrigin]);

        $coordDestination = sprintf('%s,%s', $request->get('destination_lat'), $request->get('destination_lon'));
        $resultDestination = Geo::getRegional($coordDestination, true);
        if ($resultDestination == null) throw Error::make(Response::RC_INVALID_DATA, ['message' => 'Destination not found', 'coord' => $coordDestination]);

        $handling_price = 0;
        switch ($req['moto_cc']) {
            case 150:
                $handling_price = 150000;
                break;
            case 250:
                $handling_price = 250000;
                break;
            case 999:
                $handling_price = 500000;
                break;
        }

        $weight = 0;
        if ($req['is_insured']) {
            $weight = PricingCalculator::getWeightBorne(
                $req['height'],
                $req['length'],
                $req['width'],
                0, // set weight to 0
                1, // qty
                [Handling::TYPE_WOOD]
            );
        }

        $pickup_price = 0;
        $insurance = 0;
        $service_price = 0; // todo get from regional mapping

        $price = $insurance + $handling_price + $pickup_price + $service_price;

        $result = [
            'price' => $price,
            'details' => [
                'insurance' => $insurance,
                'weight' => $weight,
                'handling' => $handling_price,
                'pickup_price' => $pickup_price,
                'service' => $service_price
            ]
        ];

        return (new Response(Response::RC_SUCCESS, $result))->json();
    }
}
