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
use App\Casts\Package\Items\Handling;
use App\Events\Deliveries\Pickup\DriverUnloadedPackageInWarehouse;
use App\Events\Payment\Nicepay\PaymentIsCorporateMode;
use App\Exceptions\DataNotFoundException;
use App\Exceptions\OutOfRangePricingException;
use App\Jobs\Packages\CreateWalkinOrder;
use App\Jobs\Packages\CustomerUploadPackagePhotos;
use App\Exceptions\Error;
use App\Http\Controllers\Api\Order\MotorBikeController;
use App\Jobs\Packages\Actions\AssignFirstPartnerToPackage;
use App\Jobs\Packages\Motobikes\CreateWalkinOrderTypeBike;
use App\Models\Customers\Customer;
use App\Models\PackageCorporate;
use App\Models\Packages\MotorBike;
use App\Models\Packages\MultiDestination;
use App\Models\Packages\Package;
use App\Models\Packages\Price as PackagesPrice;
use App\Models\Partners\Partner;
use App\Models\Partners\Transporter;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Validator;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use App\Models\Payments\Gateway;
use App\Models\Payments\Payment;
use App\Models\User;
use App\Supports\DistanceMatrix;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

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
        $tempData = PricingCalculator::corporateCalculate($payload, 'array');
        throw_if($tempData['result']['service'] == 0, OutOfRangePricingException::make(Response::RC_OUT_OF_RANGE));

        return PricingCalculator::corporateCalculate($payload);
    }

    public function store(Request $request)
    {
        $isAdmin = auth()->user()->is_admin;

        $rules = [
            'service_code' => ['required', 'in:tps,tpx'],
            'sender_name' => ['required'],
            'sender_phone' => ['required'],

            'items' => ['required'],
            'payment_method' => ['required', 'in:' . implode(',', PackageCorporate::CORPORATE_PAYMENT_ALL)],

            'receiver_name' => ['required'],
            'receiver_phone' => ['required'],
            'receiver_address' => ['required'],

            'destination_regency_id' => ['required', 'exists:geo_regencies,id'],
            'destination_district_id' => ['required', 'exists:geo_districts,id'],
            'destination_sub_district_id' => ['required', 'exists:geo_sub_districts,id'],

            'discount_service' => ['nullable']
        ];

        if (!is_null($request->get('customer_id'))) {
            $rules['customer_id'] = ['required', 'numeric', 'exists:customers,id'];
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
        if (!$hasCustomerAcc) {
            $inputs['customer_id'] = 0;
        }

        // add partner code
        $inputs['partner_code'] = $partner->code;
        $inputs['order_type'] = 'other';
        $items = json_decode($request->input('items'), true);
        $payment_method = $request->get('payment_method');

        foreach ($items ?? [] as $key => $item) {
            $items[$key] = (new Collection($item))->toArray();
        }

        // check price and tier is available or not
        $price = PricingCalculator::getPrice($partner->geo_province_id, $partner->geo_regency_id, $inputs['destination_id']);
        $totalWeight = array_sum(array_column($items, 'weight'));

        // double check
        try {
            PricingCalculator::getTier($price, $totalWeight);
        } catch (OutOfRangePricingException $e) {
            return (new Response(Response::RC_OUT_OF_RANGE, ['message' => 'Price is not available to this destination']))->json();
        } catch (\Exception $e) {
            report($e);
            return (new Response(Response::RC_OUT_OF_RANGE, ['message' => 'Something wrong']))->json();
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
            'moto' => false, // not a motobike,
            'discount_service' => isset($inputs['discount_service']) ? $inputs['discount_service'] : 0 // discount service
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

        // ovveride package_prices table if give param discount_service
        if (isset($inputs['discount_service'])) {
            $job->package->prices()->create([
                'package_id' => $job->package->id,
                'type' => PackagesPrice::TYPE_DISCOUNT,
                'description' => PackagesPrice::TYPE_SERVICE,
                'amount' => $inputs['discount_service'],
                'created_at' => now(),
                'updated_at' => now()
            ]);

            $job->package->setAttribute('total_amount', PricingCalculator::getPackageTotalAmount($job->package, false))->save();
        }

        return (new Response(Response::RC_SUCCESS, $job->package))->json();
    }

    public function storeBike(Request $request)
    {
        $isAdmin = auth()->user()->is_admin;

        $rules = [
            'service_code' => ['required', 'in:tps,tpx'],
            'sender_name' => ['required'],
            'sender_phone' => ['required'],

            'payment_method' => ['required', 'in:va,top,cash'],

            'receiver_name' => ['required'],
            'receiver_phone' => ['required'],
            'receiver_address' => ['required'],

            'destination_regency_id' => ['required', 'exists:geo_regencies,id'],
            'destination_district_id' => ['required', 'exists:geo_districts,id'],
            'destination_sub_district_id' => ['required', 'exists:geo_sub_districts,id'],
            'discount_service' => ['nullable']
        ];

        if (!is_null($request->get('customer_id'))) {
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

        // motobike validation
        if (true) {
            $rules['items.*.moto_type'] = ['required', 'in:matic,gigi,kopling'];
            $rules['items.*.moto_merk'] = ['required'];
            $rules['items.*.moto_cc'] = ['required', 'in:150,250,999'];
            $rules['items.*.moto_year'] = ['required', 'numeric'];
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
        if (!$hasCustomerAcc) {
            $inputs['customer_id'] = 0;
        }

        // add partner code
        $inputs['partner_code'] = $partner->code;
        $inputs['order_type'] = 'bike';
        $items = json_decode($request->input('items'), true);
        $payment_method = $request->get('payment_method');

        $bike = null;
        foreach ($items ?? [] as $key => $item) {
            $bike = [
                'type' => $item['moto_type'],
                'merk' => $item['moto_merk'],
                'cc' => $item['moto_cc'],
                'years' => $item['moto_year'],
                'package_id' => null,
                'package_item_id' => null
            ];
            $items[$key] = (new Collection($item))->toArray();
        }

        if (is_null($bike)) {
            return (new Response(Response::RC_INVALID_DATA, ['message' => 'No item submit']))->json();
        }

        $isSeparate = false;
        $job = new CreateWalkinOrderTypeBike($inputs, $items[$key], $isSeparate, $bike);
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
            'moto' => true, // only for motobike,
            'discount_service' => isset($inputs['discount_service']) ? $inputs['discount_service'] : 0
        ];

        PackageCorporate::create([
            'package_id' => $job->package->getKey(),
            'payment_method' => $payment_method,
            'meta' => $metaCorporate,
        ]);

        if (in_array($payment_method, [PackageCorporate::CORPORATE_PAYMENT_CASH, PackageCorporate::CORPORATE_PAYMENT_TOP])) {
            $job->package->refresh();
            $job->package->payment_status = Package::PAYMENT_STATUS_PAID;
            $job->package->status = Package::STATUS_WAITING_FOR_PACKING;
            $job->package->save();

            // trigger sla
            event(new PaymentIsCorporateMode($job->package));
        }
        if ($payment_method == PackageCorporate::CORPORATE_PAYMENT_VA) {
            // go to different method: paymentMethod, paymentMethodSet
        }

        // ovveride package_prices table if give param discount_service
        if (isset($inputs['discount_service'])) {
            $job->package->prices()->create([
                'package_id' => $job->package->id,
                'type' => PackagesPrice::TYPE_DISCOUNT,
                'description' => PackagesPrice::TYPE_SERVICE,
                'amount' => $inputs['discount_service'],
                'created_at' => now(),
                'updated_at' => now()
            ]);

            $job->package->setAttribute('total_amount', PricingCalculator::getPackageTotalAmount($job->package, false))->save();
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
            // only va method
            return $r->type == PackageCorporate::CORPORATE_PAYMENT_VA;
        })->values()->map(function ($r) use ($gatewayChoosed, $picture) {
            $select = false;
            if (!is_null($gatewayChoosed) && $r->channel == $gatewayChoosed->gateway->channel) {
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
            if (!is_null($pickupFee)) {
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
            'search' => ['nullable'],
            'status' => ['nullable', 'in:paid,draft,pending'],
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $isAdmin = auth()->user()->is_admin;

        // DB::enableQueryLog();

        $results = Package::query()
            ->with([
                'corporate', 'items' => function ($q) {
                    $q->with('codes', 'prices');
                },
                'prices', 'payments',
                'origin_regency' => function ($q) {
                    $q->with(['province']);
                },
                'destination_regency' => function ($q) {
                    $q->with(['province']);
                },
                'origin_district',
                'destination_district', 'destination_sub_district',
                'code', 'attachments', 'multiDestination.packages.code',
                'parentDestination' => function ($q) {
                    $q->with(['packages' => function ($q) {
                        $q->with(['corporate', 'code']);
                    }]);
                },
                // 'parentDestination.packages.corporate', 'parentDestination.packages.code',
                'picked_up_by'
            ]);

        if (!$isAdmin) {
            $partner = auth()->user()->partners->first();
            if (!is_null($partner)) {
                $partnerId = $partner->getKey();
                $results = $results->where(function ($q) use ($partnerId) {
                    $q->where('created_by', auth()->id())
                        ->orWhereHas('deliveries', function ($q2) use ($partnerId) {
                            $q2->where('partner_id', $partnerId);
                        });
                });
            }
        }
        if ($request->get('status')) {
            $results = $results->where('payment_status', $request->get('status'));
        }
        if ($request->get('start_date')) {
            $results = $results->whereRaw("DATE(packages.created_at) >= '" . $request->get('start_date') . "'");
        }
        if ($request->get('end_date')) {
            $results = $results->whereRaw("DATE(packages.created_at) <= '" . $request->get('end_date') . "'");
        }
        if ($request->get('search')) {
            $search = $request->get('search');
            $results = $results->where(function ($q) use ($search) {
                $q
                    ->where('sender_name', 'ILIKE', '%' . $search . '%')
                    ->orWhere('sender_phone', 'ILIKE', '%' . $search . '%')
                    ->orWhere('receiver_name', 'ILIKE', '%' . $search . '%')
                    ->orWhere('receiver_phone', 'ILIKE', '%' . $search . '%')
                    ->orWhereHas('code', function ($q) use ($search) {
                        $q->where('content', 'ILIKE', $search . '%');
                    });
            });
            $results = $results->orWhereHas('origin_regency', function ($q) use ($search) {
                $q->where('name', 'ilike', '%' . $search . '%');
            });
            $results = $results->orWhereHas('destination_regency', function ($q) use ($search) {
                $q->where('name', 'ilike', '%' . $search . '%');
            });
        }

        $results = $results->latest()->paginate(request('per_page', 15));

        $results->getCollection()->transform(function ($item) {
            $type2 = 'single';
            $payment_method = 'va';
            if (!is_null($item->corporate)) {
                $payment_method = $item->corporate->payment_method;
            }

            if ($item->multiDestination->count()) $type2 = 'multi';
            if (!is_null($item->parentDestination)) {
                $payment_method = 'va';
                if (!is_null($item->parentDestination->packages->corporate)) {
                    $payment_method = $item->parentDestination->packages->corporate->payment_method;
                }
                $type2 = 'multi';
                $item->payment_status = $item->parentDestination->packages->payment_status;
            }
            $item->type2 = $type2;

            $item->payment_method = $payment_method;
            $item->status_label = Package::statusParser($item->status);

            $item->items->map(function ($r) {
                $r->category_item_name = $r->categories ? $r->categories->name : null;
                return $r;
            });

            return $item;
        });

        return (new Response(Response::RC_SUCCESS, $results))->json();
    }

    public function countOrder(Request $request)
    {
        $isAdmin = auth()->user()->is_admin;

        $query = Package::query();

        if (!$isAdmin) {
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
                'motoBikes',
                'multiDestination.packages.code', 'parentDestination.packages.corporate', 'parentDestination.packages.code',
            ])
            // ->whereHas('corporate')
            ->findOrFail($request->get('package_id'));

        $payment = null;
        if ($result->payments->count()) {
            $payment = $result->payments->sortByDesc('id')->first();
            $bank = null;
            if (!is_null($payment->gateway)) {
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

        # new object for get payment transaction
        $trxPayment = null;
        $arrTrxPayment = null;
        if ($result->payments->count()) {
            $bank = null;
            $trxPayment = $result->payments()->where('status', Payment::STATUS_SUCCESS)->first();
            if (is_null($trxPayment)) {
                $trxPayment = $result->payments()->latest()->first();
            }

            $arrTrxPayment = [
                'bank' => ($trxPayment->gateway?->bank ?? $trxPayment->sender_bank),
                'amount' => $trxPayment->total_payment,
                'admin' => $trxPayment->payment_admin_charges,
                'expired_at' => $trxPayment->expired_at,
                'paid_at' => $trxPayment->confirmed_at ? $trxPayment->confirmed_at->format('Y-m-d H:i:s') : null,
                'status' => $trxPayment->status,
            ];
        }

        $result->trx_payment = $arrTrxPayment;
        unset($result->payments);

        $partner = null;
        // partner get from pickup
        if (is_null($partner)) {
            $partnerPickup = $result->picked_up_by->where('type', 'pickup')->first();
            if (!is_null($partnerPickup)) {
                $partner = $partnerPickup->partner;
            }
        }
        $result->partner = $partner;
        $result->total_handling_price = intval($result->prices()->where('type', 'handling')->sum('amount') ?? 0);

        $transporterDetail = null;
        if (!is_null($result->transporter_type)) {
            $transporterDetail = collect(Transporter::getDetailAvailableTypes())->map(function ($r) {
                $result = [
                    'type' => $r['name'],
                    'max_height' => $r['height'],
                    'max_width' => $r['width'],
                    'max_length' => $r['length'],
                    'max_weight' => $r['weight'],
                    'images_url' => $r['path_icons']
                ];

                return $result;
            })->where('type', $result->transporter_type)->first();
        }
        $result->transporter_detail = $transporterDetail;

        $result->price = Price::query()
            ->where('origin_regency_id', $result->origin_regency_id)
            ->where('destination_id', $result->destination_sub_district_id)
            ->first();

        return (new Response(Response::RC_SUCCESS, $result))->json();
    }

    public function pricingBike(Request $request): JsonResponse
    {
        $isAdmin = auth('api')->user()->is_admin;

        $rules = [
            'destination_id' => 'nullable|exists:geo_sub_districts,id',

            'moto_type' => 'required|in:matic,kopling,gigi',
            'moto_cc' => ['required',  Rule::in(MotorBike::getListCc())],

            /**Handling */
            'handling' => 'nullable|in:' . Handling::TYPE_WOOD,
            'height' => 'required_if:handling,wood|numeric',
            'length' => 'required_if:handling,wood|numeric',
            'width' => 'required_if:handling,wood|numeric',

            /**Pickup Fee */
            'transporter_type' => 'nullable',

            /**Insurance Price */
            'price' => 'nullable',
            'discount_service' => 'nullable'
        ];

        if ($isAdmin) {
            $rules['partner_code'] = 'required|exists:partners,code';
        }

        $request->validate($rules);
        $req = $request->all();

        /** @var Partner $partner */
        if ($isAdmin) {
            $partner = Partner::where('code', $req['partner_code'])->firstOrFail();
        } else {
            $partners = auth()->user()->partners;
            if ($partners->count() == 0) {
                return (new Response(Response::RC_INVALID_DATA, ['message' => 'No partner found']))->json();
            }
            $partner = $partners->first();
        }

        $pickup_price = 0;
        $insurance = 0;
        $insurance = ceil(MotorBikeController::getInsurancePrice($request->input('price')));

        if (!isset($req['destination_id'])) {
            return (new Response(Response::RC_INVALID_DATA, ['message' => 'No destination found']))->json();
        }

        $getPrice = PricingCalculator::getBikePrice($partner->geo_regency_id, $req['destination_id']);
        $service_price = 0; // todo get from regional mapping

        $cc = (int)$request->get('moto_cc');
        switch (true) {
            case $cc <= 149:
                $service_price = $getPrice->lower_cc;
                break;
            case $cc === 150:
                $service_price = $getPrice->middle_cc;
                break;
            case $cc >= 250:
                $service_price = $getPrice->high_cc;
                break;
        }

        $handlingPrice = Handling::bikeCalculator($request->get('moto_cc'));

        // set discount
        $discount = 0;
        if (isset($req['discount_service'])) {
            $discount = $req['discount_service'];
            $maxDiscount = $service_price * 0.3;

            if ($discount > $maxDiscount) {
                return (new Response(Response::RC_BAD_REQUEST, ['message' => 'Discount has more than from max discount, discount not more from max discount']))->json();
            }
        }

        $total_amount = $pickup_price + $insurance + $service_price + $handlingPrice - $discount;

        $result = [
            'details' => [
                'pickup_price' => $pickup_price,
                'insurance_price' => $insurance,
                'handling_price' => $handlingPrice,
                'handling_additional_price' => 0,
                'service_price' => intval($service_price),
                'discount_service' => $discount,
                'platform_fee' => PackagesPrice::FEE_PLATFORM,
            ],
            'total_amount' => $total_amount,
            'notes' => $getPrice->notes
        ];

        return (new Response(Response::RC_SUCCESS, $result))->json();
    }
}
