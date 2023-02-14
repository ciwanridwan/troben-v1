<?php

namespace App\Http\Controllers\Api;

use App\Actions\Payment\Nicepay\RegistrationPayment;
use App\Http\Response;
use App\Models\Geo\Regency;
use App\Models\Price;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Actions\Pricing\PricingCalculator;
use App\Events\Deliveries\Pickup\DriverUnloadedPackageInWarehouse;
use App\Events\Payment\Nicepay\PaymentIsCorporateMode;
use App\Exceptions\DataNotFoundException;
use App\Exceptions\OutOfRangePricingException;
use App\Jobs\Packages\CreateWalkinOrder;
use App\Jobs\Packages\CustomerUploadPackagePhotos;
use App\Exceptions\Error;
use App\Jobs\Packages\Actions\AssignFirstPartnerToPackage;
use App\Models\Customers\Customer;
use App\Models\PackageCorporate;
use App\Models\Packages\MultiDestination;
use App\Models\Packages\Package;
use App\Models\Packages\Price as PackagesPrice;
use App\Models\Partners\Partner;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Validator;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use App\Models\Payments\Gateway;
use App\Models\Payments\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class CorporateController extends Controller
{
    public function partnerList(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'nullable',
        ]);

        $q = $request->get('q');
        $result = Partner::query()
            ->select('id', 'name', 'geo_province_id', 'geo_regency_id', 'geo_district_id', 'code')
            ->where('name', 'ILIKE', '%'.$q.'%')
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
        throw_if(is_null($customer), DataNotFoundException::make(Response::RC_DATA_NOT_FOUND));

        $result = [
            'id' => $customer->getKey(),
            'name' => $customer->name,
            'phone' => $customer->phone,
        ];

        return (new Response(Response::RC_SUCCESS, $result))->json();
    }

    public function calculate(Request $request): JsonResponse
    {
        $isAdmin = auth()->user()->is_admin;

        $request->validate([
            'is_multi' => ['nullable', 'boolean'],
            'destination_id' => ['required'],
            'service_code' => ['required', 'in:tps,tpx'],
        ]);

        if ($isAdmin) {
            $rules['partner_id'] = ['required', 'numeric'];
        }

        $items = $request->get('items', []);
        if (is_array($items) && count($items) == 0) {
            $result = [
                'price' => null,
                'items' => [],
                'result' => [
                    'insurance_price_total' => 0,
                    'total_weight_borne' => 0,
                    'handling' => 0,
                    'pickup_price' => 0,
                    'discount' => 0,
                    'tier' => 0,
                    'additional_price' => 0,
                    'service' => 0,
                    'total_amount' => 0,
                ],
            ];
            return (new Response(Response::RC_SUCCESS, $result))->json();
        }

        if ($isAdmin) {
            $partner = Partner::findOrFail($request->get('partner_id'));
        } else {
            $partners = auth()->user()->partners;
            if ($partners->count() == 0) {
                return (new Response(Response::RC_INVALID_DATA, ['message' => 'No partner found']))->json();
            }
            $partner = $partners->first();
        }

        $destination_id = $request->get('destination_id');
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
        $isAdmin = auth()->user()->is_admin;

        $rules = [
            'service_code' => ['required', 'in:tps,tpx'],
            'sender_name' => ['required'],
            'sender_phone' => ['required'],

            'items' => ['required'],
            'payment_method' => ['required', 'in:va,top,cash'],

            'receiver_name' => ['required'],
            'receiver_phone' => ['required'],
            'receiver_address' => ['required'],

            'destination_regency_id' => ['required', 'exists:geo_regencies,id'],
            'destination_district_id' => ['required', 'exists:geo_districts,id'],
            'destination_sub_district_id' => ['required', 'exists:geo_sub_districts,id'],
        ];

        if (! is_null($request->get('customer_id'))) {
            $rules['customer_id'] = ['required', 'exists:customers,id'];
            $hasCustomerAcc = true;
        } else {
            $rules['customer_name'] = ['required'];
            $rules['customer_phone'] = ['required', 'numeric'];
            $hasCustomerAcc = false;
        }

        if ($isAdmin) {
            $rules['partner_code'] = ['required'];
        }

        $request->validate($rules);

        $inputs = $request->except('photos');

        /** @var Partner $partner */
        if ($isAdmin) {
            $partner = Partner::where('code', $inputs['partner_code'])->firstOrFail();
        } else {
            $partners = auth()->user()->partners;
            if ($partners->count() == 0) {
                return (new Response(Response::RC_INVALID_DATA, ['message' => 'No partner found']))->json();
            }
            $partner = $partners->first();
        }

        $inputs['sender_address'] = $partner->geo_address;
        $inputs['origin_regency_id'] = $partner->geo_regency_id;
        $inputs['origin_district_id'] = $partner->geo_district_id;
        $inputs['origin_sub_district_id'] = $partner->geo_sub_district_id;
        $inputs['sender_way_point'] = $partner->address;
        $inputs['sender_latitude'] = $partner->latitude;
        $inputs['sender_longitude'] = $partner->longitude;
        $inputs['destination_id'] = $inputs['destination_sub_district_id'];
        if (! $hasCustomerAcc) {
            $inputs['customer_id'] = 0;
        }

        // add partner code
        $inputs['partner_code'] = $partner->code;
        $inputs['order_type'] = 'other';
        $items = json_decode($request->input('items'), true);
        $payment_method = $request->get('payment_method');

        foreach ($items??[] as $key => $item) {
            $items[$key] = (new Collection($item))->toArray();
        }

        $job = new CreateWalkinOrder($inputs, $items);
        $this->dispatchNow($job);

        $photos = $request->file('photos') ?? [];
        $uploadJob = new CustomerUploadPackagePhotos($job->package, $photos);
        $this->dispatchNow($uploadJob);

        $job = new AssignFirstPartnerToPackage($job->package, $partner);
        $this->dispatch($job);

        $delivery = $job->delivery;
        event(new DriverUnloadedPackageInWarehouse($delivery));

        $job->package->setAttribute('status', Package::STATUS_WAITING_FOR_APPROVAL)->save();

        $customer = [];
        foreach (['customer_id', 'customer_name', 'customer_phone'] as $k) {
            if (isset($inputs[$k])) {
                $customer[$k] = $inputs[$k];
            }
        }

        $metaCorporate = [
            'is_multi' => false,
            'childs_id' => [],
            'parent_id' => null,
            'is_child' => false,
            'is_parent' => false,
            'order_from' => $isAdmin ? 'ho' : 'partner',
            'customer' => $customer,
        ];
        PackageCorporate::create([
            'package_id' => $job->package->getKey(),
            'payment_method' => $payment_method,
            'meta' => $metaCorporate,
        ]);

        if (in_array($payment_method, ['cash', 'top'])) {
            $job->package->refresh();
            $job->package->payment_status = Package::PAYMENT_STATUS_PAID;
            $job->package->status = Package::STATUS_WAITING_FOR_PACKING;
            $job->package->save();

            // trigger sla
            event(new PaymentIsCorporateMode($job->package));
        }
        if ($payment_method == 'va') {
            // go to different method: paymentMethod, paymentMethodSet
        }

        return (new Response(Response::RC_SUCCESS, $job->package))->json();
    }

    public function paymentMethod(Request $request)
    {
        $request->validate([
            'package_id' => ['required', 'numeric'],
        ]);

        $package = Package::findOrFail($request->package_id);

        $gateway = Gateway::query()
            ->get([
                'id',
                'channel',
                'name',
                'is_fixed',
                'admin_charges'
            ]);

        $picture = Storage::disk('s3')->temporaryUrl('nopic.png', Carbon::now()->addMinutes(60));

        $gatewayChoosed = $package
            ->payments
            ->where('status', Payment::STATUS_PENDING)
            ->first();
        $gateway = $gateway->filter(function ($r) {
            return $r->type == 'va';
        })->values()->map(function ($r) use ($gatewayChoosed, $picture) {
            $select = false;
            if (! is_null($gatewayChoosed) && $r->channel == $gatewayChoosed->gateway->channel) {
                $select = true;
            }

            $bankPicture = $picture;
            $filePath = sprintf('asset/bank/%s.png', $r->bank);
            if (Storage::disk('s3')->exists($filePath)) {
                $bankPicture = Storage::disk('s3')->temporaryUrl($filePath, Carbon::now()->addMinutes(60));
            }

            $r->picture = $bankPicture;
            $r->selecteable = $select;
            return $r;
        });

        return (new Response(Response::RC_SUCCESS, $gateway))->json();
    }

    public function paymentMethodSet(Request $request)
    {
        $request->validate([
            'package_id' => ['required', 'numeric'],
            'payment_channel' => ['required'],
        ]);

        $package = Package::findOrFail($request->package_id);

        $gatewayChoosed = $package
            ->payments
            ->where('status', Payment::STATUS_PENDING);
        if ($gatewayChoosed->count()) {
            foreach ($gatewayChoosed as $pg) {
                $pg->status = Payment::STATUS_CANCELLED;
                $pg->save();
            }
        }

        $gateway = Gateway::where('channel', $request->get('payment_channel'))->firstOrFail();

        $result = (new RegistrationPayment($package, $gateway))->vaRegistration();

        return (new Response(Response::RC_SUCCESS, $result))->json();
    }

    public function storeMulti(Request $request)
    {
        $request->validate([
            'package_parent_id' => ['required', 'numeric'],

            'package_child_ids' => ['array'],
            'package_child_ids.*' => ['required', 'numeric'],
        ]);

        $parentPackage = Package::findOrFail($request->package_parent_id);
        $childPackageIDs = (array) $request->package_child_ids;

        $childPackages = [];
        foreach ($childPackageIDs as $c) {
            $childPackages[] = Package::findOrFail($c);
        }

        $metaCorporate = PackageCorporate::where('package_id', $request->package_parent_id)->firstOrFail();
        $meta = $metaCorporate->meta;
        $meta['is_multi'] = true;
        $meta['childs_id'] = collect($childPackages)->pluck('id');
        $meta['parent_id'] = $parentPackage->getKey();
        $meta['is_parent'] = true;
        $metaCorporate->meta = $meta;
        $metaCorporate->save();

        foreach ($childPackages as $childPkg) {
            $pickupFee = $childPkg->prices->where('type', PackagesPrice::TYPE_DELIVERY)->where('description', PackagesPrice::TYPE_PICKUP)->first();
            if (! is_null($pickupFee)) {
                $childPkg->total_amount -= $pickupFee->amount;
                $childPkg->save();

                $pickupFee->amount = 0;
                $pickupFee->save();
            }

            $metaCorporate = PackageCorporate::where('package_id', $childPkg->getKey())->firstOrFail();
            $meta = $metaCorporate->meta;
            $meta['is_multi'] = true;
            $meta['childs_id'] = collect($childPackages)->pluck('id');
            $meta['parent_id'] = $parentPackage->getKey();
            $meta['is_parent'] = false;
            $metaCorporate->meta = $meta;
            $metaCorporate->save();

            MultiDestination::create([
                'parent_id' => $parentPackage->getKey(),
                'child_id' => $childPkg->getKey(),
            ]);
        }

        return (new Response(Response::RC_CREATED))->json();
    }

    public function listOrder(Request $request)
    {
        $request->validate([
            'status' => ['nullable', 'in:paid,draft,pending'],
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $isAdmin = auth()->user()->is_admin;

        $results = Package::query()->with([
            'corporate',
            'items', 'prices', 'payments', 'items.codes', 'origin_regency.province', 'origin_regency', 'origin_district', 'destination_regency.province',
            'destination_regency', 'destination_district', 'destination_sub_district', 'code', 'items.prices', 'attachments',
            'multiDestination', 'parentDestination',
        //])->whereHas('corporate');
        ]);

        if (! $isAdmin) {
            $results = $results->where('created_by', auth()->id());
        }
        if ($request->get('status')) {
            $results = $results->where('payment_status', $request->get('status'));
        }
        if ($request->get('start_date')) {
            $results = $results->whereRaw("DATE(packages.created_at) >= '".$request->get('start_date')."'");
        }
        if ($request->get('end_date')) {
            $results = $results->whereRaw("DATE(packages.created_at) <= '".$request->get('end_date')."'");
        }

        $results = $results->latest()->paginate(request('per_page', 15));

        $results->getCollection()->transform(function ($item) {
            $type2 = 'single';
            if ($item->multiDestination->count()) {
                $type2 = 'multi';
            }
            if (! is_null($item->parentDestination)) {
                $type2 = 'multi';
            }
            $item->type2 = $type2;

            return $item;
        });

        return (new Response(Response::RC_SUCCESS, $results))->json();
    }

    public function countOrder(Request $request)
    {
        $isAdmin = auth()->user()->is_admin;

        $query = Package::query()->whereHas('corporate');

        if (! $isAdmin) {
            $query = $query->where('created_by', auth()->id());
        }

        $count = $query->count();

        $results = [
            'total' => $count,
        ];

        return (new Response(Response::RC_SUCCESS, $results))->json();
    }

    public function detailOrder(Request $request)
    {
        $request->validate([
            'package_id' => ['required', 'numeric'],
        ]);

        $result = Package::query()
            ->with([
                'corporate', 'payments', 'attachments',
                'items', 'prices', 'items.codes', 'origin_regency.province', 'origin_regency', 'origin_district', 'destination_regency.province',
                'destination_regency', 'destination_district', 'destination_sub_district', 'code', 'items.prices',
            ])
            // ->whereHas('corporate')
            ->findOrFail($request->get('package_id'));

        $payment = null;
        if ($result->payments->count()) {
            $payment = $result->payments->sortByDesc('id')->first();
            $bank = null;
            if (! is_null($payment->gateway)) {
                $bankPicture = Storage::disk('s3')->temporaryUrl('nopic.png', Carbon::now()->addMinutes(60));
                $filePath = sprintf('asset/bank/%s.png', $payment->gateway->bank);
                if (Storage::disk('s3')->exists($filePath)) {
                    $bankPicture = Storage::disk('s3')->temporaryUrl($filePath, Carbon::now()->addMinutes(60));
                }

                $bank = [
                    'name' => $payment->gateway->bank,
                    'picture' => $bankPicture,
                ];
            }
            $payment->bank = $bank;
            unset($payment->gateway);
        }
        $result->payment = $payment;
        unset($result->payments);

        $partner = null;
        $creator = User::find($result->created_by);
        if (! is_null($creator)) {
            $partners = $creator->partners;
            if ($partners->count()) {
                $partner = $partners->first();
            }
        }
        $result->partner = $partner;

        $result->price = Price::where('destination_id', $result->destination_sub_district->id)->where('zip_code', $result->destination_sub_district->zip_code)->first();

        return (new Response(Response::RC_SUCCESS, $result))->json();
    }
}
