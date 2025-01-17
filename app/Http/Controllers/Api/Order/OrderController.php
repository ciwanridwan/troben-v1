<?php

namespace App\Http\Controllers\Api\Order;

use App\Actions\Pricing\PricingCalculator;
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
use App\Exceptions\InvalidDataException;
use App\Exceptions\OutOfRangePricingException;
use App\Exceptions\UserUnauthorizedException;
use App\Http\Resources\Api\Package\PackageResource;
use App\Jobs\Packages\CustomerUploadPackagePhotos;
use App\Models\Code;
use App\Models\CodeLogable;
use App\Models\Packages\BikePrices;
use App\Models\Partners\ScheduleTransportation;
use App\Models\Partners\VoucherAE;
use App\Models\Service;
use App\Supports\Geo;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\Api\Pricings\CheckPriceResource;
use App\Jobs\Packages\Actions\MultiAssignFirstPartner;
use App\Models\Packages\CubicPrice;
use App\Models\Packages\ExpressPrice;
use App\Models\Packages\MultiDestination;
use App\Models\Payments\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

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
            fn (Builder $query) => $query->orderByDesc('created_at'),
        );
        $query->when($request->input('status'), fn (Builder $builder, $status) => $builder->whereIn('status', Arr::wrap($status)));

        $query->with('origin_regency', 'destination_regency', 'destination_district', 'destination_sub_district', 'motoBikes', 'multiDestination', 'parentDestination');

        // $query->whereDoesntHave('parentDestination');

        $paginate = $query->paginate();
        $itemCollection = $paginate->getCollection()->filter(function ($r) {
            // todo if status is paid return true
            if ($r->multiDestination->count()) {
                return true;
            }

            if (!is_null($r->parentDestination)) {
                if ($r->payment_status === Package::PAYMENT_STATUS_PAID) {
                    return true;
                } else {
                    return false;
                }
            }
            return true;
        })->values();

        $paginate->setCollection($itemCollection);
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
            'voucher_code' => ['nullable'],
            'partner_id' => ['nullable'],
        ]);

        $multiDestination = $package->multiDestination()->get();

        $multiPrices = null;
        $multiItems = null;
        $isMulti = false;
        $isMultiApprove = false;

        if ($multiDestination->isNotEmpty()) {
            if ($package->payment_status !== Package::PAYMENT_STATUS_PAID) {
                $isMulti = true;

                $childId = $multiDestination->pluck('child_id')->toArray();
                $check = Package::whereIn('id', $childId)->get()->filter(function ($q) {
                    if ($q->status === Package::STATUS_WAITING_FOR_APPROVAL) {
                        return false;
                    }
                    return true;
                });

                if ($check->isEmpty() && $package->status === Package::STATUS_WAITING_FOR_APPROVAL) {
                    $isMultiApprove = true;
                }
            }
            $multiPrices = PricingCalculator::getDetailMultiPricing($package);
            $multiItems = PricingCalculator::getDetailMultiItems($package);
        }

        $prices = PricingCalculator::getDetailPricingPackage($package);

        $service_discount = $package->prices()->where('type', PackagePrice::TYPE_DISCOUNT)->where('description', PackagePrice::TYPE_SERVICE)->get()->sum('amount');
        $prices['voucher_price_discount'] = 0;
        if ($request->promotion_hash && $service_discount == 0) {
            $promo = $this->check($request->promotion_hash, $package);
            $prices['service_price_fee'] = $promo['service_price_fee'];
            $prices['service_price_discount'] = $promo['service_price_discount'];
        } elseif ($request->voucher_code && $request->promotion_hash == null) {
            $voucher = $this->claimVoucher($request->voucher_code, $package, $request->get('partner_id'));
            $prices['service_price_fee'] = 0;
            $prices['service_price_discount'] = $voucher['service_price_discount'];
            $prices['voucher_price_discount'] = $voucher['voucher_price_discount'];
            if (isset($voucher['pickup_price_discount'])) { // free pickup
                $prices['pickup_price_discount'] = $voucher['pickup_price_discount'];
            }
        }

        // override if already inputed
        if ($package->claimed_voucher && $package->claimed_voucher->voucher && $package->claimed_voucher->voucher->aevoucher) {
            $aevoucher = $package->claimed_voucher->voucher->aevoucher;
            $voucher = $this->claimVoucher($aevoucher->code, $package, $aevoucher->partner_id);
            $prices['service_price_fee'] = 0;
            $prices['service_price_discount'] = $voucher['service_price_discount'];
            $prices['voucher_price_discount'] = $voucher['voucher_price_discount'];
            $prices['pickup_price_discount'] = $voucher['pickup_price_discount'] ?? 0; // free pickup
        }

        /** Old script */
        $package->load(
            'canceled',
            'code',
            'prices',
            'attachments',
            'items',
            'items.attachments',
            'items.prices',
            'motoBikes',
            'deliveries.partner',
            'deliveries.assigned_to.userable',
            'deliveries.assigned_to.user',
            'origin_regency',
            'destination_regency',
            'destination_district',
            'destination_sub_district',
            'multiDestination.packages.code',
            // 'multiParents'
        )->append('transporter_detail');

        /** Price Of Item */
        $price = Price::query()
            ->where('origin_regency_id', $package->origin_regency_id)
            ->where('destination_id', $package->destination_sub_district_id)
            ->first();

        /** Price for motobikes */
        $bikePrice = BikePrices::query()
            ->where('origin_regency_id', $package->origin_regency_id)
            ->where('destination_id', $package->destination_sub_district_id)->first();


        $serviceCode = $package->service_code;

        $cubicPrice = $package->prices()->where('type', PackagePrice::TYPE_SERVICE)->where('description', PackagePrice::DESCRIPTION_TYPE_CUBIC)->first()->amount ?? 0;
        switch ($serviceCode) {
            case Service::TRAWLPACK_STANDARD:
                $service_price = $package->prices()->where('type', PackagePrice::TYPE_SERVICE)->where('description', PackagePrice::TYPE_SERVICE)->first()->amount ?? $cubicPrice;
                break;

            default:
                $service_price = $package->prices()->where('type', PackagePrice::TYPE_SERVICE)->where('description', PackagePrice::DESCRIPTION_TYPE_EXPRESS)->first()->amount ?? $cubicPrice;
                break;
        }

        /**Set condition for retrieve type by motobikes or items */
        if ($package['motoBikes'] !== null) {
            $result['type'] = 'bike';
            $result['notes'] = $bikePrice->notes ?? '';
            $result['packing_price'] = $package->prices()->where('type', PackagePrice::TYPE_HANDLING)->where('description', PackagePrice::DESCRIPTION_TYPE_BIKE)->get()->sum('amount');
            $result['packing_additional_price'] = $package->prices()->where('type', PackagePrice::TYPE_HANDLING)->where('description', PackagePrice::DESCRIPTION_TYPE_WOOD)->get()->sum('amount');
        } else {
            $result['type'] = 'item';
            $result['notes'] = $price->notes ?? '';
            $result['packing_price'] = $prices['packing_price'];
        }

        $getFeeAdditional = $package->prices()->where('type', PackagePrice::TYPE_SERVICE)->where('description', PackagePrice::TYPE_ADDITIONAL)->first();
        if (is_null($getFeeAdditional)) {
            $feeAdditional = 0;
        } else {
            $feeAdditional = $getFeeAdditional->amount;
        }
        $checkPayment = Payment::with('gateway')->where('payable_id', $package->id)
            ->where('payable_type', Package::class)->first();

        $isWalkin = is_null($package->transporter_type) ? 'walkin' : 'app';

        $driver = null;
        if (isset($package->deliveries) && count($package->deliveries)) {
            foreach ($package->deliveries->sortByDesc('created_at') as $d) {
                if (isset($d->assigned_to) && $d->assigned_to != null) {
                    $driver = $d->assigned_to;
                    $driver->partner = $d->partner;
                    break;
                }
            }
        }

        $payment = Payment::with('gateway')
            ->where('payable_type', Package::class)
            ->where('payable_id', $package->id)
            ->where('service_type', 'pay')
            ->where('status', ['pending', 'success'])
            ->latest()
            ->first();

        // complaint, rating and review
        $ratingAndReview = $package->ratings ?? null;
        if (!is_null($ratingAndReview)) {
            $ratingResult = [
                'rating' => $ratingAndReview->rating,
                'description' => $ratingAndReview->review,
                'created_at' => $ratingAndReview->created_at->format('Y-m-d H:i:s')
            ];
        } else {
            $ratingResult = null;
        }


        $complaint = $package->complaints ? $package->complaints->only('type', 'desc', 'created_at') : null;
        $imageComplaint = $package->complaints ? $package->complaints->meta : null;

        if (!is_null($imageComplaint) && !is_null($complaint)) {
            $complaintImages = ['photos' => json_decode($imageComplaint)];
            $complaintUrlImage = [];

            $photos = (array)$complaintImages['photos'];
            foreach ($photos['photos'] as $photo) {
                $url = generateUrl($photo);
                array_push($complaintUrlImage, $url);
            }
            $complaint = array_merge($complaint, ["photos" => $complaintUrlImage]);
            $complaint['created_at'] = Carbon::parse($complaint['created_at'])->format('Y-m-d H:i:s');
        } else {
            $complaint = null;
        }
        
        $package->items->map(function ($r) use ($package) {
            $r->attachments_item = $package->attachments ?? [];
            $r->category_item_name = $r->categories ? $r->categories->name : null;

            return $r;
        });

        $data = [
            'type' => $result['type'],
            'notes' => $result['notes'],
            'service_price' => $service_price,
            // 'service_price_fee' => $prices['service_price_fee'] ?? 0,
            'service_price_discount' => $prices['service_price_discount'] ?? 0,
            'insurance_price' => $prices['insurance_price'] ?? 0,
            'insurance_price_discount' => $prices['insurance_price_discount'] ?? 0,
            'packing_price' => $result['packing_price'] ?? 0,
            'packing_additional_price' => $result['packing_additional_price'] ?? 0,
            'packing_price_discount' => $prices['packing_price_discount'] ?? 0,
            'pickup_price' => $prices['pickup_price'] ?? 0,
            'pickup_price_discount' => $prices['pickup_price_discount'] ?? 0,
            'voucher_price_discount' => $prices['voucher_price_discount'] ?? 0,
            'fee_additional' => $feeAdditional,
            'platform_fee' => $prices['platform_price'],
            'is_walkin' => $isWalkin,
            'total_amount' => $package->total_amount - $prices['voucher_price_discount'],
            'is_multi' => $isMulti,
            'multi_price' => $multiPrices,
            'multi_items' => $multiItems,
            'is_multi_approve' => $isMultiApprove,
            'driver' => $driver,
            'payment' => $payment,
            'review' => $ratingResult,
            'complaint' => $complaint
        ];

        // return $this->jsonSuccess(DataDiscountResource::make($data));
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

    public function claimVoucher($voucher_code, Package $package, $partnerId): array
    {
        $voucher = Voucher::where('code', $voucher_code)->first();

        if (!$voucher) {
            $default =  [
                'service_price_fee' =>  0,
                'voucher_price_discount' => 0,
                'service_price_discount' => 0,
            ];

            // add fallback to VoucherAE generated
            if ($partnerId != null) {
                $voucherAE = VoucherAE::query()
                    ->where('is_approved', true)
                    ->where('partner_id', (int) $partnerId)
                    ->where('code', $voucher_code)
                    ->latest()
                    ->first();
                if ($voucherAE) {
                    return PricingCalculator::getCalculationVoucherPackageAE($voucherAE, $package);
                }
            }

            return $default;
        }

        if (!is_null($voucher->aevoucher)) {
            return PricingCalculator::getCalculationVoucherPackageAE($voucher->aevoucher, $package);
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
            'items.*.is_glassware' => ['nullable'],
            'photos' => ['nullable'],
            'photos.*' => ['nullable', 'image'],
            'destination_regency_id' => ['required', 'exists:geo_regencies,id'],
            'destination_district_id' => ['required', 'exists:geo_districts,id'],
            'destination_sub_district_id' => ['required', 'exists:geo_sub_districts,id']
        ]);

        $origin_regency_id = $request->get('origin_regency_id');
        // $destination_id = $request->get('destination_regency_id');
        if ($origin_regency_id == null) {
            // add validation
            $request->validate([
                'origin_lat' => 'required|numeric',
                'origin_lon' => 'required|numeric',
                // 'destination_lat' => 'required|numeric',
                // 'destination_lon' => 'required|numeric',
            ]);

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
            // $destination_id = $resultDestination['district'];
            $request->merge([
                'origin_regency_id' => $origin_regency_id,
                'destination_regency_id' => $request->get('destination_regency_id'),
                'destination_district_id' => $request->get('destination_district_id'),
                'destination_sub_district_id' => $request->get('destination_sub_district_id'),
                'sender_latitude' => $request->get('origin_lat'),
                'sender_longitude' => $request->get('origin_lon'),
                'receiver_latitude' => $request->get('destination_lat'),
                'receiver_longitude' => $request->get('destination_lon')
            ]);
        }

        $inputs = $request->except('items');

        /** @var Customer $user */
        $user = $request->user();

        /** @noinspection PhpParamsInspection */
        /** @noinspection PhpUnhandledExceptionInspection */
        throw_if(!$user instanceof Customer, UserUnauthorizedException::class, Response::RC_UNAUTHORIZED);
        /** @var Regency $regency */
        $regency = Regency::query()->findOrFail($origin_regency_id);
        $payload = array_merge($request->toArray(), ['origin_province_id' => $regency->province_id, 'destination_id' => $request->get('destination_sub_district_id')]);
        $tempData = PricingCalculator::calculate($payload, 'array');
        Log::info('New Order.', ['request' => $request->all(), 'tempData' => $tempData]);
        Log::info('Ordering service. ', ['result' => $tempData['result']['service'] != 0]);
        throw_if($tempData['result']['service'] == 0, OutOfRangePricingException::make(Response::RC_OUT_OF_RANGE));

        $inputs['customer_id'] = $user->id;

        $items = $request->input('items') ?? [];

        foreach ($items as $key => $item) {
            if (($item['insurance'] ?? '') == '1') {
                $items[$key]['is_insured'] = true;
            }
            if (($item['is_glassware'] ?? '') == '1') {
                $items[$key]['is_glassware'] = true;
            }
        }

        // validate partner code
        if (isset($items['partner_code'])) {
            $partner = Partner::where('code', $items['partner_code'])->findOrFail();
            if (is_null($partner)) {
                throw InvalidDataException::make(Response::RC_INVALID_DATA, ['message' => 'Partner not found', 'code' => $items['partner_code']]);
            }
        }

        $job = new CreateNewPackage($inputs, $items);

        $this->dispatchNow($job);
        Log::info('after dispatch job. ', [$request->get('sender_name')]);

        $uploadJob = new CustomerUploadPackagePhotos($job->package, $request->file('photos') ?? []);

        $this->dispatchNow($uploadJob);

        /**Simple response with needed frontend */
        $data = ['hash' => $job->package->hash];

        return (new Response(Response::RC_CREATED, $data))->json();

        /** Old response */
        // return $this->jsonSuccess(new PackageResource($job->package->load(
        //     'items',
        //     'prices',
        //     'origin_regency',
        //     'destination_regency',
        //     'destination_district',
        //     'destination_sub_district'
        // )));
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
        throw_if(!$user instanceof Customer, Error::class, Response::RC_UNAUTHORIZED);

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
            'voucher_code' => ['nullable'],
            'partner_id' => ['nullable'],
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
            $pickup_discount_price = $package->prices()->where('type', PackagesPrice::TYPE_DISCOUNT)
                ->where('description', PackagesPrice::TYPE_PICKUP)->first();
            if ($pickup_discount_price) {
                $pickup_discount_price->delete();
            }
            $voucher = Voucher::where('code', $request->voucher_code)->first();
            if (!$voucher) {
                $partnerId = $request->get('partner_id');
                if ($partnerId != null) {
                    // add fallback to Voucher AE
                    $voucherAE = VoucherAE::query()
                        ->where('is_approved', true)
                        ->where('partner_id', (int) $partnerId)
                        ->where('code', $request->get('voucher_code'))
                        ->latest()
                        ->first();
                    if ($voucherAE) {
                        $typeVoucher = Voucher::VOUCHER_DISCOUNT_SERVICE_PERCENTAGE;
                        $amountVoucher = 0;
                        if ($voucherAE->type == VoucherAE::VOUCHER_FREE_PICKUP) {
                            $typeVoucher = Voucher::VOUCHER_FREE_PICKUP;
                        }
                        if ($voucherAE->type == VoucherAE::VOUCHER_DISCOUNT_SERVICE) {
                            if ($voucherAE->discount > 0) {
                                $typeVoucher = Voucher::VOUCHER_DISCOUNT_SERVICE_PERCENTAGE;
                                $amountVoucher = $voucherAE->discount;
                            } elseif ($voucherAE->nominal > 0) {
                                $typeVoucher = Voucher::VOUCHER_DISCOUNT_SERVICE_NOMINAL;
                                $amountVoucher = $voucherAE->nominal;
                            }
                        }

                        $voucher = Voucher::create([
                            'user_id' => $voucherAE->user_id,
                            'title' => $voucherAE->title,
                            'partner_id' => $voucherAE->partner_id,
                            'discount' => $amountVoucher,
                            'code' => $voucherAE->code,
                            'start_date' => $voucherAE->created_at,
                            'end_date' => $voucherAE->expired,
                            'is_approved' => true,
                            'type' => $typeVoucher,
                            'aevoucher_id' => $voucherAE->getKey(),
                        ]);
                    } else {
                        return (new Response(Response::RC_DATA_NOT_FOUND, ['message' => 'Kode Voucher Tidak Ditemukan']))->json();
                    }
                } else {
                    return (new Response(Response::RC_DATA_NOT_FOUND, ['message' => 'Kode Voucher Tidak Ditemukan']))->json();
                }
            }

            $job = new ClaimDiscountVoucher($voucher, $package->id, $request->user()->id);
            $this->dispatchNow($job);
        }

        if ($package->multiDestination()->exists()) {
            $this->updatePackageMultiStatus($package);
        } else {
            event(new PackageApprovedByCustomer($package));
        }

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
        if (!$code->exists) {
            $request->validate([
                'code' => ['required', 'exists:codes,content']
            ]);

            /** @var Code $code */
            $code = Code::query()->where('content', 'ILIKE', '%' . $request->code . '%')->first();
        }

        $codeable = $code->codeable;

        throw_if(!$codeable instanceof Package, ValidationException::withMessages([
            'code' => __('Code not instance of Package'),
        ]));

        $package = $codeable->load(['origin_regency', 'destination_district', 'destination_district.regency'])->only([
            'sender_name',
            'receiver_name',
            'origin_regency',
            'destination_district',
            'status',
            'payment_status',
        ]);

        $statusLabel = $package['status'];
        switch ($statusLabel) {
            case Package::STATUS_CANCEL:
                $statusLabel = 'Pesan Dibatalkan';
                break;
            case Package::STATUS_CANCEL_SELF_PICKUP:
                $statusLabel = 'Pesan Dibatalkan dan paket akan diambil kembali oleh Customer';
                break;
            case Package::STATUS_CANCEL_DELIVERED:
                $statusLabel = 'Pesan Dibatalkan dan paket akan diantar kembali oleh Mitra';
                break;
            case Package::STATUS_CREATED:
                $statusLabel = 'Menunggu Assign Mitra';
                break;
            case Package::STATUS_ACCEPTED:
                if ($package['payment_status'] == Package::PAYMENT_STATUS_DRAFT) {
                    $statusLabel = 'Menunggu Pembayaran Customer';
                }
                if ($package['payment_status'] == Package::PAYMENT_STATUS_PENDING) {
                    $statusLabel = 'Menunggu konfirmasi pembayaran oleh Admin';
                }
                break;
            case Package::STATUS_WAITING_FOR_PAYMENT:
                $statusLabel = 'Menunggu konfirmasi pembayaran oleh Admin';
                break;
            case Package::STATUS_WAITING_FOR_PICKUP:
                $statusLabel = 'Menunggu Penjemputan';
                break;
            case Package::STATUS_PICKED_UP:
                $statusLabel = 'Driver telah menerima barang';
                break;
            case Package::STATUS_WAITING_FOR_ESTIMATING:
                $statusLabel = 'Menunggu untuk dilakukan pengecekan di gudang';
                break;
            case Package::STATUS_ESTIMATING:
                $statusLabel = 'Sedang pengecekan di gudang';
                break;
            case Package::STATUS_ESTIMATED:
                $statusLabel = 'Selesai pengecekan di gudang';
                break;
            case Package::STATUS_WAITING_FOR_PACKING:
                $statusLabel = 'Sedang menunggu packing';
                break;
            case Package::STATUS_PACKING:
                $statusLabel = 'Sedang dipacking';
                break;
            case Package::STATUS_PACKED:
                $statusLabel = 'Telah selesai packing';
                break;
            case Package::STATUS_WAITING_FOR_APPROVAL:
                $statusLabel = 'Menunggu konfirmasi customer';
                break;
            case Package::STATUS_REVAMP:
                $statusLabel = 'Resi sedang ditinjau ulang oleh Kasir Mitra';
                break;
            case Package::STATUS_MANIFESTED:
                $statusLabel = 'Telah terassign di manifest';
                break;
            case Package::STATUS_IN_TRANSIT:
                $statusLabel = 'Barang sedang di proses mitra';
                break;
            case Package::STATUS_WITH_COURIER:
                $statusLabel = 'Barang sedang di antar kurir';
                break;
            case Package::STATUS_PENDING:
                $statusLabel = 'Mitra belum melakukan penerimaan pesanan';
                break;
            case Package::STATUS_DELIVERED:
                $statusLabel = 'Barang sudah diterima';
                break;
        }
        $package['status_label'] = $statusLabel;
        $package['receipt_code'] = $request->code;
        unset($package['status']);
        unset($package['payment_status']);

        /** @var Builder $query */
        $query = $code->logs()->getQuery();
        // $query->selectRaw('min(status) as status, min(description) as description, min(id) as id, min(created_at) as created_at');
        $query->where('status', '!=', CodeLogable::TYPE_SCAN);
        $query->whereJsonContains('showable', CodeLogable::SHOW_CUSTOMER);
        // $query->groupBy('status');
        $query->orderBy('created_at', 'desc');

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

    public function usePersonalData(Request $request)
    {
        $user = $request->user();

        $result = [
            'name' => $user->name,
            'phone' => $user->phone
        ];

        return (new Response(Response::RC_SUCCESS, $result))->json();
    }

    public function chooseDeliveryMethod(Request $request): JsonResponse
    {
        $this->attributes = $request->validate(
            [
                'origin_lat' => ['nullable', 'numeric'],
                'origin_lon' => ['nullable', 'numeric'],
                'destination_id' => ['nullable', 'numeric', 'exists:geo_sub_districts,id'],
                'service_code' => ['nullable', 'exists:services,code']
            ]
        );

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

        $originRegencyId = $resultOrigin['regency'];
        $destinationId = $this->attributes['destination_id'];
        $serviceCode = $this->attributes['service_code'];

        return $this->getPrice($serviceCode, $originRegencyId, $destinationId);
    }

    /**
     * asdasds.
     */
    public function storeMultiDestination(Request $request)
    {
        $request->validate([
            'package_parent_hash' => ['nullable', 'string'],
            'package_child_hash' => ['nullable', 'array'],
        ]);

        if (is_null($request->package_parent_hash) || is_null($request->package_child_hash)) {
	// assume it created, to allow ios skip
        return (new Response(Response::RC_CREATED))->json();


            return (new Response(Response::RC_BAD_REQUEST, ['message' => 'Package hash or child hash is null, cant given null value']))->json();
        }

        $parentPackage = Package::hashToId($request->package_parent_hash);
        $childPackage = $request->package_child_hash;

        $childIds = [];
        for ($i = 0; $i < count($childPackage); $i++) {
            $childId = Package::hashToId($childPackage[$i]);
            array_push($childIds, $childId);

            MultiDestination::create([
                'parent_id' => $parentPackage,
                'child_id' => $childId
            ]);
        }
        $packageChild = Package::whereIn('id', $childIds)->get()->each(function ($q) {
            $pickupFee = $q->prices->where('type', PackagePrice::TYPE_DELIVERY)->where('description', PackagePrice::TYPE_PICKUP)->first();

            $q->total_amount -= $pickupFee->amount;
            $q->save();

            $pickupFee->amount = 0;
            $pickupFee->save();
        });
        return (new Response(Response::RC_CREATED))->json();
    }

    public function multiOrderAssignation(Request $request, Partner $partner)
    {
        $inputs = $request->validate([
            'package_hash' => ['nullable', 'array']
        ]);

        $package = $inputs['package_hash'];
        $packages = [];
        for ($i = 0; $i < count($package); $i++) {
            $data = ['package_id' => Package::hashToId($package[$i])];

            array_push($packages, $data);
        }

        $type = 'old';
        $job = new MultiAssignFirstPartner($packages, $partner, $type);
        $this->dispatchNow($job);

        return (new Response(Response::RC_SUCCESS, $job->packages))->json();
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
            fn ($q) => $q->where('name', 'like', '%' . $this->attributes['q'] . '%')
        );

        return $builder;
    }

    private function getPrice($serviceCode, $originRegencyId, $destinationId): JsonResponse
    {
        switch ($serviceCode) {
            case Service::TRAWLPACK_STANDARD:
                $prices = Price::where('origin_regency_id', $originRegencyId)
                    ->where('destination_id', $destinationId)
                    ->where('service_code', $serviceCode)
                    ->first();

                if (is_null($prices)) {
                    $message = ['message' => 'Lokasi tujuan belum tersedia, silahkan hubungi customer kami'];
                    return (new Response(Response::RC_SUCCESS, $message))->json();
                }

                return $this->jsonSuccess(CheckPriceResource::make($prices));
                break;

            case Service::TRAWLPACK_CUBIC:
                $prices = CubicPrice::where('origin_regency_id', $originRegencyId)
                    ->where('destination_id', $destinationId)
                    ->where('service_code', $serviceCode)
                    ->first();

                if (is_null($prices)) {
                    $message = ['message' => 'Lokasi tujuan belum tersedia, silahkan hubungi customer kami'];
                    return (new Response(Response::RC_SUCCESS, $message))->json();
                }

                return $this->jsonSuccess(CheckPriceResource::make($prices));
                break;
            case Service::TRAWLPACK_EXPRESS:
                $prices = ExpressPrice::where('origin_regency_id', $originRegencyId)
                    ->where('destination_id', $destinationId)
                    ->where('service_code', $serviceCode)
                    ->first();

                if (is_null($prices)) {
                    $message = ['message' => 'Lokasi tujuan belum tersedia, silahkan hubungi customer kami'];
                    return (new Response(Response::RC_SUCCESS, $message))->json();
                }

                return $this->jsonSuccess(CheckPriceResource::make($prices));
                break;
        }
    }

    private function updatePackageMultiStatus($package)
    {
        $childId = $package->multiDestination()->get()->pluck('child_id')->toArray();
        $packageChild = Package::whereIn('id', $childId)->get();

        $packageChild->each(function ($q) {
            throw_if($q->status !== Package::STATUS_WAITING_FOR_APPROVAL, ValidationException::withMessages([
                'package' => __('package should be in ' . Package::STATUS_WAITING_FOR_APPROVAL . ' status'),
            ]));

            $q->setAttribute('status', Package::STATUS_ACCEPTED)
                ->setAttribute('payment_status', Package::PAYMENT_STATUS_PENDING)
                ->setAttribute('updated_by', User::USER_SYSTEM_ID)
                ->save();

            return $q;
        });

        event(new PackageApprovedByCustomer($package));
    }

    public function test($code)
    {
        $test = Code::query();

        $test->with(['codeable', 'logs']);

        $data = $test->where('content', $code)->where('codeable_type', Package::class)->first(); 
        $log = $data->logs()->orderBy('created_at', 'desc')->get()->map(function ($q) {
            $result = [
                'st,atus' => $q->status,
                'desc' => $q->description,
                'time_at' => $q->created_at->format('Y-m-d')
            ];

            return $result;
        });

        $res = [
            'package' => $data->codeable,
            'log' => $log  
        ];

        return (new Response(Response::RC_SUCCESS, $res))->json();
    }
}
