<?php

namespace App\Http\Controllers\Api\Order;

use App\Actions\Pricing\PricingCalculator;
use App\Events\Partners\PartnerCashierDiscount;
use App\Http\Resources\Account\CourierResource;
use App\Http\Resources\FindReceiptResource;
use App\Http\Resources\Promote\DataDiscountResource;
use App\Http\Response;
use App\Exceptions\Error;
use App\Jobs\Packages\Actions\AssignFirstPartnerToPackage;
use App\Jobs\Promo\ClaimExistingPromo;
use App\Jobs\Voucher\ClaimDiscountVoucher;
use App\Models\Geo\Regency;
use App\Models\Packages\Price as PackagePrice;
use App\Models\Packages\Price as PackagesPrice;
use App\Models\Partners\Partner;
use App\Models\Partners\Voucher;
use App\Models\Price;
use App\Models\Promos\ClaimedPromotion;
use App\Models\Promos\Promotion;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Models\Packages\Package;
use Illuminate\Http\JsonResponse;
use App\Models\Customers\Customer;
use App\Http\Controllers\Controller;
use App\Jobs\Packages\CreateNewPackage;
use Illuminate\Database\Eloquent\Builder;
use App\Jobs\Packages\CustomerUploadReceipt;
use App\Jobs\Packages\UpdateExistingPackage;
use App\Events\Packages\PackageApprovedByCustomer;
use App\Http\Resources\Api\Package\PackageResource;
use App\Jobs\Packages\CustomerUploadPackagePhotos;
use App\Models\Code;
use App\Models\CodeLogable;
use App\Models\Partners\ScheduleTransportation;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    /**
     * Filtered attributes.
     *
     * @var array
     */
    protected $attributes;

    public function index(Request $request): JsonResponse
    {
        $query = $request->user()->packages();
        $query->when(
            $request->input('order'),
            fn (Builder $query, string $order) => $query->orderBy($order, $request->input('order_direction', 'asc')),
            fn (Builder $query) => $query->orderByDesc('created_at')
        );

        $query->when($request->input('status'), fn (Builder $builder, $status) => $builder->whereIn('status', Arr::wrap($status)));

        $query->with('origin_regency', 'destination_regency', 'destination_district', 'destination_sub_district');

        $paginate = $query->paginate();

        return $this->jsonSuccess(PackageResource::collection($paginate));
    }

    /**
     * @param Package $package
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function show(Request $request, Package $package): JsonResponse
    {
        $this->authorize('view', $package);
        $request->validate([
            'promotion_hash' => ['nullable'],
            'voucher_code' => ['nullable']
        ]);

        $prices = PricingCalculator::getDetailPricingPackage($package);
        $service_discount = $package->prices()->where('type', PackagePrice::TYPE_DISCOUNT)->where('description', PackagePrice::TYPE_SERVICE)->get()->sum('amount');
        $prices['voucher_price_discount'] = 0;
        if ($request->promotion_hash && $service_discount == 0) {
            $promo = $this->check($request->promotion_hash, $package);
            $prices['service_price_fee'] = $promo['service_price_fee'];
            $prices['service_price_discount'] = $promo['service_price_discount'];
        } elseif ($request->voucher_code && $request->promotion_hash == null) {
            $voucher = $this->claimVoucher($request->voucher_code, $package);
            $prices['service_price_fee'] = 0;
            $prices['service_price_discount'] = $voucher['service_price_discount'];
            $prices['voucher_price_discount'] = $voucher['voucher_price_discount'];
        }

        $package->load(
            'code',
            'prices',
            'attachments',
            'items',
            'items.attachments',
            'items.prices',
            'deliveries.partner',
            'deliveries.assigned_to.userable',
            'deliveries.assigned_to.user',
            'origin_regency',
            'destination_regency',
            'destination_district',
            'destination_sub_district'
        )->append('transporter_detail');

        $price = Price::query()
            ->where('origin_regency_id', $package->origin_regency_id)
            ->where('destination_id', $package->destination_sub_district_id)
            ->first();
        $service_price = $package->prices()->where('type', PackagePrice::TYPE_SERVICE)->where('description', PackagePrice::TYPE_SERVICE)->get()->sum('amount');
        $data = [
            'notes' => $price->notes,
            'service_price' => $service_price,
            'service_price_fee' => $prices['service_price_fee'] ?? 0,
            'service_price_discount' => $prices['service_price_discount'] ?? 0,
            'insurance_price' => $prices['insurance_price'] ?? 0,
            'insurance_price_discount' => $prices['insurance_price_discount'] ?? 0,
            'packing_price' => $prices['packing_price'] ?? 0,
            'packing_price_discount' => $prices['packing_price_discount'] ?? 0,
            'pickup_price' => $prices['pickup_price'] ?? 0,
            'pickup_price_discount' => $prices['pickup_price_discount'] ?? 0,
            'voucher_price_discount' => $prices['voucher_price_discount'] ?? 0,

            'total_amount' => $package->total_amount - $prices['voucher_price_discount']
        ];

        return $this->jsonSuccess(DataDiscountResource::make(array_merge($package->toArray(), $data)));
    }

    public function check($promotion_hash, Package $package): array
    {
        $promotion = ClaimedPromotion::where('customer_id', $package->customer_id)->latest()->first();
        switch ($promotion) {
            case null:
                return PricingCalculator::getCalculationPromoPackage($promotion_hash, $package);
            default:
                if ($promotion_hash != null) {
                    if ($promotion->updated_at < $promotion->updated_at->addDays(1)) {
                        return PricingCalculator::getCalculationPromoPackage($promotion_hash, $package);
                    }
                }
        }
    }

    public function claimVoucher($voucher_code, Package $package): array
    {
        $voucher = Voucher::where('code', $voucher_code)->first();

        if (! $voucher) {
            return [
                'service_price_fee' =>  0,
                'voucher_price_discount' => 0,
            ];
        }
        return PricingCalculator::getCalculationVoucherPackage($voucher, $package);
    }

    /**
     * Create new order
     * Route Path       : {API_DOMAIN}/order
     * Route Method     : POST
     * Route Name       : api.order.store.
     *
     * @param \Illuminate\Http\Request $request
     * @return JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Throwable
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'items' => ['required'],
            'items.*.is_insured' => ['nullable'],
            'photos' => ['nullable'],
            'photos.*' => ['nullable', 'image']
        ]);

        $inputs = $request->except('items');

        /** @var Customer $user */
        $user = $request->user();

        /** @noinspection PhpParamsInspection */
        /** @noinspection PhpUnhandledExceptionInspection */
        throw_if(! $user instanceof Customer, Error::class, Response::RC_UNAUTHORIZED);
        /** @var Regency $regency */
        $regency = Regency::query()->find($request->get('origin_regency_id'));
        $tempData = PricingCalculator::calculate(array_merge($request->toArray(), ['origin_province_id' => $regency->province_id, 'destination_id' => $request->get('destination_sub_district_id')]), 'array');
        Log::info('New Order.', ['request' => $request->all(), 'tempData' => $tempData]);
        Log::info('Ordering service. ', ['result' => $tempData['result']['service'] != 0]);
        throw_if($tempData['result']['service'] == 0, Error::make(Response::RC_OUT_OF_RANGE));

        $inputs['customer_id'] = $user->id;

        $items = $request->input('items') ?? [];

        foreach ($items as $key => $item) {
            if ($item['insurance'] == '1') {
                $items[$key]['is_insured'] = true;
            }
        }
        $job = new CreateNewPackage($inputs, $items);

        $this->dispatchNow($job);
        Log::info('after dispatch job. ', [$request->get('sender_name')]);

        $uploadJob = new CustomerUploadPackagePhotos($job->package, $request->file('photos') ?? []);

        $this->dispatchNow($uploadJob);

        return $this->jsonSuccess(new PackageResource($job->package->load(
            'items',
            'prices',
            'origin_regency',
            'destination_regency',
            'destination_district',
            'destination_sub_district'
        )));
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param Package $package
     * @return JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Throwable
     */
    public function update(Request $request, Package $package): JsonResponse
    {
        $this->authorize('update', $package);

        $inputs = $request->all();

        /** @var Customer $user */
        $user = $request->user();

        /** @noinspection PhpParamsInspection */
        /** @noinspection PhpUnhandledExceptionInspection */
        throw_if(! $user instanceof Customer, Error::class, Response::RC_UNAUTHORIZED);

        $job = new UpdateExistingPackage($package, $inputs);

        $this->dispatchNow($job);

        return $this->jsonSuccess(new PackageResource($job->package->load(
            'prices',
            'origin_regency',
            'destination_regency',
            'destination_district',
            'destination_sub_district'
        )));
    }

    /**
     * @param Request $request
     * @param Package $package
     * @return JsonResponse
     * @throws AuthorizationException
     * @throws \Throwable
     */
    // Approving Order by Customer
    public function approve(Request $request, Package $package): JsonResponse
    {
        $this->authorize('update', $package);
        $request->validate([
            'promotion_hash' => ['nullable'],
            'voucher_code' => ['nullable']
        ]);
        if ($request->promotion_hash != null) {
            $promotion = Promotion::byHashOrFail($request->promotion_hash);
            $job = new ClaimExistingPromo($promotion, $package);
            $this->dispatchNow($job);
        }

        if ($request->voucher_code != null) {
            $service_discount_price = $package->prices()->where('type', PackagesPrice::TYPE_DISCOUNT)
                ->where('description', PackagesPrice::TYPE_SERVICE)->first();
            if ($service_discount_price) {
                $service_discount_price->delete();
            }
            $voucher = Voucher::where('code', $request->voucher_code)->first();
            if (! $voucher) {
                return (new Response(Response::RC_DATA_NOT_FOUND, ['message' => 'Kode Voucher Tidak Ditemukan']))->json();
            }
            $service_price = $package->prices()->where('type', PackagePrice::TYPE_SERVICE)->where('description', PackagePrice::TYPE_SERVICE)->get()->sum('amount');
            $service_discount_price = $package->prices()->where('type', PackagePrice::TYPE_DISCOUNT)->where('description', PackagePrice::TYPE_SERVICE)->get()->sum('amount');
            $percentage_discount = $service_discount_price / $service_price * 100;
            if ($percentage_discount < $voucher->discount) {
                $job = new ClaimDiscountVoucher($voucher, $package->id, $request->user()->id);
                $this->dispatchNow($job);
            }
        }
        event(new PackageApprovedByCustomer($package));
        //        event(new PartnerCashierDiscount($package));

        return $this->jsonSuccess(PackageResource::make($package->fresh()));
    }

    /**
     * @param Package $package
     * @param Partner $partner
     * @return JsonResponse
     */
    public function orderAssignation(Package $package, Partner $partner): JsonResponse
    {
        $job = new AssignFirstPartnerToPackage($package, $partner);
        $this->dispatchNow($job);

        return (new Response(Response::RC_SUCCESS, $job->package))->json();
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param Package $package
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function receipt(Request $request, Package $package): JsonResponse
    {
        $this->authorize('update', $package);

        $request->validate([
            'receipt' => 'required|image',
        ]);

        $job = new CustomerUploadReceipt($package, $request->file('receipt'));

        $this->dispatchNow($job);

        return $this->jsonSuccess(PackageResource::make($job->package->load('attachments')));
    }

    /**
     * @param Request $request
     * @param Code $code
     *
     * @return JsonResponse
     */
    public function findReceipt(Request $request, Code $code): JsonResponse
    {
        if (! $code->exists) {
            $request->validate([
                'code' => ['required', 'exists:codes,content']
            ]);

            /** @var Code $code */
            $code = Code::query()->where('content', $request->code)->first();
        }

        $codeable = $code->codeable;

        throw_if(! $codeable instanceof Package, ValidationException::withMessages([
            'code' => __('Code not instance of Package'),
        ]));

        $package = $codeable->load(['origin_regency', 'destination_district', 'destination_district.regency'])->only([
            'sender_name',
            'receiver_name',
            'origin_regency',
            'destination_district',
        ]);

        /** @var Builder $query */
        $query = $code->logs()->getQuery();
        // $query->selectRaw('min(status) as status, min(description) as description, min(id) as id, min(created_at) as created_at');
        $query->where('status', '!=', CodeLogable::TYPE_SCAN);
        $query->whereJsonContains('showable', CodeLogable::SHOW_CUSTOMER);
        // $query->groupBy('status');
        $query->orderBy('id');

        return $this->jsonSuccess(FindReceiptResource::make([
            'package' => $package,
            'track' => $query->get()
        ]));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function courierList(Request $request): JsonResponse
    {
        $this->attributes = Validator::make($request->all(), [
            'regency_id' => 'nullable',
        ])->validate();

        $query = $this->getBasicBuilder(User::query());
        $query->where('is_active', true);
        $query->whereNotNull('latitude');
        $query->whereNotNull('longitude');

        $query->whereHas('role', function (Builder $query) {
            $query->where('role', 'driver');
            $query->where('userable_type', 'App\Models\Partners\Transporter');
        });
        $query->when(request()->has('regency_id'), fn ($q) => $q->where('regency_id', $this->attributes['regency_id']));

        return $this->jsonSuccess(CourierResource::collection($query->paginate(request('per_page', 15))));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * Add Ship Schedule Features in way to create order in customer apps.
     */
    public function shipSchedule(Request $request): JsonResponse
    {
        $this->attributes = Validator::make($request->all(), [
            'origin_regency_id' => 'required',
            'destination_regency_id' => 'required',
        ])->validate();

        $schedules = ScheduleTransportation::where('origin_regency_id', $request->origin_regency_id)
            ->where('destination_regency_id', $request->destination_regency_id)
            ->orderByRaw('updated_at - created_at desc')->first();

        if ($schedules == null) {
            return (new Response(Response::RC_DATA_NOT_FOUND))->json();
        } else {
            $result = ScheduleTransportation::where('origin_regency_id', $request->origin_regency_id)
                ->where('destination_regency_id', $request->destination_regency_id)
                ->orderByRaw('departed_at asc')->get();

            $result->makeHidden(['created_at', 'updated_at', 'deleted_at', 'harbor_id']);
            return (new Response(Response::RC_SUCCESS, $result))->json();
        }
    }

    /**
     * @param Builder $builder
     * @return Builder
     */
    private function getBasicBuilder(Builder $builder): Builder
    {
        $builder->when(request()->has('id'), fn ($q) => $q->where('id', $this->attributes['id']));
        $builder->when(
            request()->has('q') and request()->has('id') === false,
            fn ($q) => $q->where('name', 'like', '%'.$this->attributes['q'].'%')
        );

        return $builder;
    }
}
