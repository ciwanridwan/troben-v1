<?php

namespace App\Http\Controllers\Api;

use App\Http\Response;
use App\Models\Geo\Regency;
use App\Models\Price;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\PriceResource;
use Illuminate\Database\Eloquent\Builder;
use App\Actions\Pricing\PricingCalculator;
use App\Events\Deliveries\Pickup\DriverUnloadedPackageInWarehouse;
use App\Exceptions\InvalidDataException;
use App\Http\Resources\Api\Pricings\CheckPriceResource;
use App\Models\Packages\CubicPrice;
use App\Models\Packages\ExpressPrice;
use App\Exceptions\OutOfRangePricingException;
use App\Jobs\Packages\CreateWalkinOrder;
use App\Jobs\Packages\CustomerUploadPackagePhotos;
use App\Models\Partners\ScheduleTransportation;
use App\Models\Service;
use App\Supports\Geo;
use App\Exceptions\Error;
use App\Jobs\Packages\Actions\AssignFirstPartnerToPackage;
use App\Models\Customers\Customer;
use App\Models\PackageCorporate;
use App\Models\Packages\MultiDestination;
use App\Models\Packages\Package;
use App\Models\Packages\Price as PackagesPrice;
use App\Models\Partners\Partner;
use App\Supports\Repositories\PartnerRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Validator;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

class CorporateController extends Controller
{
    public function partnerList(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'nullable',
        ]);

        $q = $request->get('q');
        $result = Partner::query()
            ->select('id', 'name', 'geo_province_id', 'geo_regency_id', 'geo_district_id')
            ->where('name', 'ILIKE', '%' . $q . '%')
            ->where('type', Partner::TYPE_BUSINESS)
            ->whereNotNull(['latitude', 'longitude'])
            ->where('availability', 'open')
            ->where('is_show', true)
            ->get();

        return (new Response(Response::RC_SUCCESS, $result))->json();
    }

    public function customerList(Request $request): JsonResponse
    {
        $phoneNumber =
            PhoneNumberUtil::getInstance()->format(
                PhoneNumberUtil::getInstance()->parse($request->phone, 'ID'),
                PhoneNumberFormat::E164
            );

        Validator::validate([
            'phone' => $phoneNumber
        ], [
            'phone' => ['required']
        ]);

        $customer = Customer::select('id', 'name', 'phone')->where('phone', $phoneNumber)->first();
        throw_if(is_null($customer), Error::make(Response::RC_DATA_NOT_FOUND));

        $result = [
            'id' => $customer->getKey(),
            'name' => $customer->name,
            'phone' => $customer->phone,
        ];

        return (new Response(Response::RC_SUCCESS, $result))->json();
    }

    public function calculate(Request $request): JsonResponse
    {
        $request->validate([
            'is_multi' => ['nullable', 'boolean'],
            'destination_id' => ['required'],
	        'service_code' => ['required', 'in:tps,tpx'],
            'partner_id' => ['required', 'numeric'],
        ]);

        $destination_id = $request->get('destination_id');
        $partner = Partner::findOrFail($request->get('partner_id'));
        throw_if(is_null($partner->regency), Error::make(Response::RC_PARTNER_GEO_UNAVAILABLE));

        $regency = $partner->regency;

        /** @var Regency $regency */
        $additional = [
            'origin_province_id' => $regency->province_id,
            'origin_regency_id' => $regency->id,
            'destination_id' => $destination_id
        ];
        $request->merge(['is_multi' => $request->is_multi ?? false]);
        $payload = array_merge($request->toArray(), $additional);
        $tempData = PricingCalculator::calculate($payload, 'array');
        throw_if($tempData['result']['service'] == 0, OutOfRangePricingException::make(Response::RC_OUT_OF_RANGE));

        return PricingCalculator::calculate($payload);
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'service_code' => ['required', 'in:tps,tpx'],
            'sender_name' => ['required'],
            'sender_phone' => ['required'],
            'partner_id' => ['required'],

            'method_payment' => ['required', 'in:cash,va,top'],

            'receiver_name' => ['required'],
            'receiver_phone' => ['required'],
            'receiver_address' => ['required'],

            'origin_regency_id' => ['required', 'exists:geo_regencies,id'],
            'destination_regency_id' => ['required', 'exists:geo_regencies,id'],
            'destination_district_id' => ['required', 'exists:geo_districts,id'],
            'destination_sub_district_id' => ['required', 'exists:geo_sub_districts,id'],
        ]);

        $inputs = $request->except('photos');

        /** @var Partner $partner */
        $partner = Partner::find($inputs['partner_id']);
        $inputs['sender_address'] = $partner->geo_address;
        $inputs['origin_regency_id'] = $partner->geo_regency_id;
        $inputs['origin_district_id'] = $partner->geo_district_id;
        $inputs['origin_sub_district_id'] = $partner->geo_sub_district_id;
        $inputs['sender_way_point'] = $partner->address;
        $inputs['sender_latitude'] = $partner->latitude;
        $inputs['sender_longitude'] = $partner->longitude;
        $inputs['destination_sub_district_id'] = $inputs['destination_id'];

        // add partner code
        $inputs['partner_code'] = $partner->code;
        $inputs['order_type'] = 'other';
        $items = $request->input('items') ?? [];

        foreach ($items as $key => $item) {
            $items[$key] = (new Collection($item))->toArray();
        }

        $job = new CreateWalkinOrder($inputs, $items);
        $this->dispatchNow($job);

        $uploadJob = new CustomerUploadPackagePhotos($job->package, $request->file('photos') ?? []);
        $this->dispatchNow($uploadJob);

        $job = new AssignFirstPartnerToPackage($job->package, $partner);
        $this->dispatch($job);

        $delivery = $job->delivery;
        event(new DriverUnloadedPackageInWarehouse($delivery));

        $job->package->setAttribute('status', Package::STATUS_WAITING_FOR_APPROVAL)->save();

        $metaCorporate = [
            'is_multi' => false,
            'childs_id' => [],
            'parent_id' => null,
            'is_child' => false,
            'is_parent' => false,
        ];
        PackageCorporate::create([
            'package_id' => $job->package->getKey(),
            'payment_method' => $request->get('payment_method'),
            'meta' => $metaCorporate,
        ]);

        // if status in cash,top
        // set auto paid
        // for va call nicepay

        // checker for multi

        // add paid at corporate

        return (new Response(Response::RC_SUCCESS, $job->package))->json();
    }

    public function storeMulti(Request $request)
    {
        $request->validate([
            'package_parent_hash' => ['nullable', 'string'],
            'package_child_hash' => ['nullable', 'array'],

            'package_parent_id' => ['array'],
            'package_child_id.*' => ['required', 'numeric'],
        ]);

        $parentPackage = Package::findOrFail($request->package_parent_id);
        $childPackage = $request->package_child_hash;

        foreach ($request->get('package_child_id') ?? [] as $c) {
        }

        $childIds = [];
        for ($i = 0; $i < count($childPackage); $i++) {
            $childId = Package::hashToId($childPackage[$i]);
            array_push($childIds, $childId);

            MultiDestination::create([
                'parent_id' => $parentPackage,
                'child_id' => $childId
            ]);
        }
        Package::whereIn('id', $childIds)->get()->each(function ($q) {
            $pickupFee = $q->prices->where('type', PackagesPrice::TYPE_DELIVERY)->where('description', PackagesPrice::TYPE_PICKUP)->first();

            $q->total_amount -= $pickupFee->amount;
            $q->save();

            $pickupFee->amount = 0;
            $pickupFee->save();
        });
        return (new Response(Response::RC_CREATED))->json();
    }
}
