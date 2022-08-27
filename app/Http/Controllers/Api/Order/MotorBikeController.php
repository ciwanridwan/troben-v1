<?php

namespace App\Http\Controllers\Api\Order;

use App\Actions\Pricing\PricingCalculator;
use App\Casts\Package\Items\Handling;
use App\Events\Packages\PackageCreated;
use App\Events\Packages\PackageCreatedForBike;
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
use App\Models\Packages\MotorBike;
use App\Models\Packages\Package;
use App\Models\Partners\Partner;
use App\Models\Partners\Transporter;
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

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'customer_id' => ['nullable', 'exists:customers,id'],
            'service_code' => ['required', 'exists:services,code'],
            'transporter_type' => ['required', Rule::in(Transporter::getAvailableTypes())],

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
        

        /**Inserting to tables */
        $data = new Package();
        $data->customer_id = $request->user()->first()->id;
        $data->service_code = $request->input('service_code');
        $data->transporter_type = $request->input('transporter_type');
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
        $data->destination_sub_district_id = $request->input('destination_sub_district');
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

        return (new Response(Response::RC_CREATED, $result))->json();
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
}
