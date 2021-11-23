<?php

namespace App\Http\Controllers\Api\Order;

use App\Actions\Pricing\PricingCalculator;
use App\Http\Resources\Account\CourierResource;
use App\Http\Resources\Promote\DataDiscountResource;
use App\Http\Response;
use App\Exceptions\Error;
use App\Jobs\Packages\Actions\AssignFirstPartnerToPackage;
use App\Jobs\Promo\ClaimExistingPromo;
use App\Models\Geo\Regency;
use App\Models\Packages\Price;
use App\Models\Partners\Partner;
use App\Models\Promos\ClaimedPromotion;
use App\Models\Promos\Promotion;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Resources\Json\JsonResource;
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
            'promotion_hash' => ['nullable']
        ]);
        if ($request->promotion_hash){
            $promo = $this->check($request->promotion_hash, $package);

            return $this->jsonSuccess(DataDiscountResource::make(array_merge($promo,$package->toArray())));
        }

        return $this->jsonSuccess(new PackageResource($package->load(
            'prices',
            'attachments',
            'items',
            'items.attachments',
            'items.prices',
            'tarif',
            'deliveries.partner',
            'deliveries.assigned_to.userable',
            'deliveries.assigned_to.user',
            'origin_regency',
            'destination_regency',
            'destination_district',
            'destination_sub_district'
        )));
    }

    public function check($promotion_hash, Package $package){

        $check = ClaimedPromotion::where('customer_id', $package->customer_id)->latest()->first();
        switch ($check){
            case null :
                return $this->calculation($promotion_hash, $package);
            default:
                if ($promotion_hash != null){
                    if ($check->updated_at < $check->updated_at->addDays(1)){

                        $data = $this->calculation($promotion_hash, $package);
                        return $data;
                    }
                }
        }
    }

    public function calculation($promotion_hash, Package $package)
    {
        $promotion = Promotion::byHashOrFail($promotion_hash);
        $prices = $package->prices()->get();
        $service = $prices->where('type', Price::TYPE_SERVICE)->first();
        $insurance = $prices->where('type', Price::TYPE_INSURANCE)->first();
        $handling = $prices->where('type', Price::TYPE_HANDLING)->first();
        $pickup = $prices->where('type', Price::TYPE_DELIVERY)->first();
        if ($package->total_weight < $promotion->min_weight){
            $discount = $service->amount * 0;
        }else{
            $discount = $service->amount - ($package->tier_price * $promotion->min_weight);
        }
        $data = [
            'promo' => [
                'pickup_price' => $pickup->amount ?? 0,
                'insurance_price' => $insurance->amount ?? 0,
                'handling_price' => $handling->amount ?? 0,
                'service_price' => $package->total_amount - $discount
            ]
        ];

        return $data;
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
        $tempData = PricingCalculator::calculate(array_merge($request->toArray(),['origin_province_id' => $regency->province_id, 'destination_id' => $request->get('destination_sub_district_id')]),'array');
        throw_if($tempData['result']['service'] == 0, Error::make(Response::RC_OUT_OF_RANGE));

        $inputs['customer_id'] = $user->id;

        $items = $request->input('items') ?? [];

        $job = new CreateNewPackage($inputs, $items);

        $this->dispatchNow($job);

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
            'promotion_hash' => ['nullable']
        ]);
        if ($request->promotion_hash != null){
            $promotion = Promotion::byHashOrFail($request->promotion_hash);
            $job = new ClaimExistingPromo($promotion, $package);
            $this->dispatchNow($job);
        }
        event(new PackageApprovedByCustomer($package));

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

        $package = $codeable->load(['origin_regency','destination_district','destination_district.regency'])->only([
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

        return (new Response(Response::RC_SUCCESS, [
            'package' => $package,
            'track' => $query->get()
        ]))->json();
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
