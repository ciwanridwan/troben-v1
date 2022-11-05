<?php

namespace App\Http\Controllers\Partner\Cashier;

use App\Events\Partners\PartnerCashierDiscount;
use App\Http\Resources\Account\UserResource;
use App\Http\Response;
use App\Jobs\Packages\UpdateOrCreatePriceFromExistingPackage;
use App\Models\Deliveries\Delivery;
use App\Models\Packages\Price;
use App\Models\Partners\Partner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Packages\Item;
use App\Models\Packages\Package;
use App\Http\Controllers\Controller;
use App\Concerns\Controllers\HasResource;
use Illuminate\Database\Eloquent\Builder;
use App\Jobs\Packages\Item\UpdateExistingItem;
use App\Events\Packages\PackageCheckedByCashier;
use App\Events\Partners\PartnerCashierDiscountForBike;
use App\Supports\Repositories\PartnerRepository;
use App\Jobs\Packages\Item\DeleteItemFromExistingPackage;

class HomeController extends Controller
{
    use HasResource;

    /**
     * @var array
     */
    protected array $attributes;

    /**
     * @var Builder
     */
    protected Builder $query;

    /**
     * @var string
     */
    protected string $model = Package::class;


    /**
     * @var array
     */
    protected array $rules = [
        'q' => ['nullable'],
    ];


    public function index(Request $request, PartnerRepository $partnerRepository)
    {
        if ($request->expectsJson()) {
            if ($request->has('partner')) {
                return (new Response(Response::RC_SUCCESS, $partnerRepository->getPartner()))->json();
            }

            $this->query = $partnerRepository->queries()->getPackagesQuery()
                ->with(
                    [
                        'items', 'prices', 'payments', 'items.codes', 'origin_regency.province', 'origin_regency', 'origin_district', 'destination_regency.province',
                        'destination_regency', 'destination_district', 'destination_sub_district', 'code', 'items.prices', 'attachments', 'motoBikes'
                    ]
                );

            $this->query->whereHas('code', function ($query) use ($request) {
                $query->whereRaw("LOWER(content) like '%" . strtolower($request->q) . "%'");
            });
            $this->attributes = $request->validate($this->rules);

            $this->query->orderBy('created_at', 'desc');
            $this->getResource();

            return (new Response(Response::RC_SUCCESS, $this->query->paginate(request('per_page', 15))))->json();
        }

        return view('partner.cashier.home.index');
    }

    public function updatePackageItem(Request $request, Package $package, Item $item)
    {
        $job = new UpdateExistingItem($package, $item, $request->toArray());

        $this->dispatch($job);

        return (new Response(Response::RC_SUCCESS, $job->item->load('prices')))->json();
    }

    public function deletePackageItem(Package $package, Item $item)
    {
        $job = new DeleteItemFromExistingPackage($package, $item);

        $this->dispatch($job);

        return (new Response(Response::RC_SUCCESS, $job->item))->json();
    }

    public function packageChecked(Package $package, Request $request)
    {
        $request->validate(
            [
                'type' => ['nullable', 'in:service,pickup'],
                'calculate_type' => ['nullable', 'in:kg,cubic']
            ]
        );

        $type = $request->type;

        if ($request->has('discount')) {
            if ($type == Price::TYPE_SERVICE) {
                switch ($request->user()->partners[0]['type']) {
                    case Partner::TYPE_BUSINESS:
                        $check = $this->check(Delivery::FEE_PERCENTAGE_BUSINESS, $package);
                        break;
                    case Partner::TYPE_SPACE:
                        $check = $this->check(Delivery::FEE_PERCENTAGE_SPACE, $package);
                        break;
                    case Partner::TYPE_POS:
                        $check = $this->check(Delivery::FEE_PERCENTAGE_POS, $package);
                        break;
                    case Partner::TYPE_HEADSALES:
                        $check = $this->check(Delivery::FEE_PERCENTAGE_HEADSALES, $package);
                        break;
                    case Partner::TYPE_SALES:
                        $check = $this->check(Delivery::FEE_PERCENTAGE_SALES, $package);
                        break;
                }

                if ($request->discount > $check) {
                    return (new Response(Response::RC_BAD_REQUEST, ['max_discount' => $check]))->json();
                }
            } else {
                switch ($request->user()->partners[0]['type']) {
                    case Partner::TYPE_BUSINESS:
                        $checkPickup = $this->checkPickup(Delivery::FEE_PERCENTAGE_BUSINESS, $package);
                        break;
                    case Partner::TYPE_SPACE:
                        $checkPickup = $this->checkPickup(Delivery::FEE_PERCENTAGE_SPACE, $package);
                        break;
                    case Partner::TYPE_POS:
                        $checkPickup = $this->checkPickup(Delivery::FEE_PERCENTAGE_POS, $package);
                        break;
                    case Partner::TYPE_HEADSALES:
                        $checkPickup = $this->checkPickup(Delivery::FEE_PERCENTAGE_HEADSALES, $package);
                        break;
                    case Partner::TYPE_SALES:
                        $checkPickup = $this->checkPickup(Delivery::FEE_PERCENTAGE_SALES, $package);
                        break;
                }
                if ($request->discount > $checkPickup) {
                    return (new Response(Response::RC_BAD_REQUEST, ['max_discount' => $checkPickup]))->json();
                }
            }

            if ($type == Price::TYPE_SERVICE) {
                $job = new UpdateOrCreatePriceFromExistingPackage($package, [
                    'type' => Price::TYPE_DISCOUNT,
                    'description' => Price::TYPE_SERVICE,
                    'amount' => $request->discount,
                ]);
                $this->dispatch($job);
            } else {
                $job = new UpdateOrCreatePriceFromExistingPackage($package, [
                    'type' => Price::TYPE_DISCOUNT,
                    'description' => Price::TYPE_PICKUP,
                    'amount' => $request->discount,
                ]);
                $this->dispatch($job);
            }

            $bikes = $package->motoBikes()->first();

            if (is_null($bikes)) {
                event(new PartnerCashierDiscount($package));
            } else {
                event(new PartnerCashierDiscountForBike($package));
            }
        }

        if ($request->calculate_type === 'cubic') {
            $this->changePriceToCubic($package);
        }

        event(new PackageCheckedByCashier($package));

        return (new Response(Response::RC_SUCCESS))->json();
    }

    public function check(float $fee_percentage, Package $package): float
    {
        $service_price = $package->prices->where('type', Price::TYPE_SERVICE)->where('description', Price::TYPE_SERVICE)->first()->amount;
        return $service_price * $fee_percentage;
    }

    /** Check the price is pickup_fee
     * And set calculate
     */
    public function checkPickup(float $feePercentage, Package $package): float
    {
        $pickupPrice = $package->prices->where('type', Price::TYPE_DELIVERY)->first()->amount;
        return $pickupPrice * $feePercentage;
    }

    public function getUserInfo(Request $request)
    {
        $account = $request->user();
        return $this->jsonSuccess(new UserResource($account));
    }

    public function getHistoryDataByPackageStatus(Request $request, $status_condition): JsonResponse
    {
        if ($request->has('partner')) {
            return $this->getPartners($request);
        }
        $this->query->whereHas('code', function ($query) use ($request) {
            $query->whereRaw("LOWER(content) like '%" . strtolower($request->q) . "%'");
        });

        $this->query->where($status_condition);
        $this->query->with(['items', 'items.prices', 'deliveries', 'deliveries.partner', 'code']);
        $this->query->orderBy('created_at', 'desc');
        // $this->query->whereDoesntHave('deliveries');

        return (new Response(Response::RC_SUCCESS, $this->query->paginate(request('per_page', 15))))->json();
    }

    public function processed(Request $request, PartnerRepository $partnerRepository)
    {
        if ($request->expectsJson()) {
            if ($request->has('partner')) {
                return (new Response(Response::RC_SUCCESS, $partnerRepository->getPartner()))->json();
            }

            $this->query = $partnerRepository->queries()->getPackagesQuery()->with(['items', 'items.codes', 'origin_regency.province', 'origin_regency', 'origin_district', 'destination_regency.province', 'destination_regency', 'destination_district', 'destination_sub_district', 'code', 'items.prices']);
            $this->query->where('status', '!=', Package::STATUS_CANCEL);
            $this->query->where('status', '!=', Package::STATUS_CREATED);
            $this->query->where('status', '!=', Package::STATUS_DELIVERED);

            $this->query->whereHas('code', function ($query) use ($request) {
                $query->whereRaw("LOWER(content) like '%" . strtolower($request->q) . "%'");
            });

            $this->attributes = $request->validate($this->rules);
            $this->getResource();

            return (new Response(Response::RC_SUCCESS, $this->query->paginate(request('per_page', 15))))->json();
        }

        return view('partner.cashier.home.index');
    }

    public function cancel(Request $request, PartnerRepository $partnerRepository)
    {
        if ($request->expectsJson()) {
            if ($request->has('partner')) {
                return (new Response(Response::RC_SUCCESS, $partnerRepository->getPartner()))->json();
            }

            $this->query = $partnerRepository->queries()->getPackagesQuery()->with(['items', 'items.codes', 'origin_regency.province', 'origin_regency', 'origin_district', 'destination_regency.province', 'destination_regency', 'destination_district', 'destination_sub_district', 'code', 'items.prices']);
            $this->query->where('status', Package::STATUS_CANCEL);

            $this->query->whereHas('code', function ($query) use ($request) {
                $query->whereRaw("LOWER(content) like '%" . strtolower($request->q) . "%'");
            });

            $this->attributes = $request->validate($this->rules);
            $this->getResource();

            return (new Response(Response::RC_SUCCESS, $this->query->paginate(request('per_page', 15))))->json();
        }

        return view('partner.cashier.home.index');
    }

    public function done(Request $request, PartnerRepository $partnerRepository)
    {
        if ($request->expectsJson()) {
            if ($request->has('partner')) {
                return (new Response(Response::RC_SUCCESS, $partnerRepository->getPartner()))->json();
            }

            $this->query = $partnerRepository->queries()->getPackagesQuery()->with(['items', 'items.codes', 'origin_regency.province', 'origin_regency', 'origin_district', 'destination_regency.province', 'destination_regency', 'destination_district', 'destination_sub_district', 'code', 'items.prices']);
            $this->query->where('status', Package::STATUS_DELIVERED);

            $this->query->whereHas('code', function ($query) use ($request) {
                $query->whereRaw("LOWER(content) like '%" . strtolower($request->q) . "%'");
            });

            $this->attributes = $request->validate($this->rules);
            $this->getResource();

            return (new Response(Response::RC_SUCCESS, $this->query->paginate(request('per_page', 15))))->json();
        }

        return view('partner.cashier.home.index');
    }

    /** Get estimation calculate cubic prices
     * And change pricing
     */
    private function changePriceToCubic($package)
    {
        $cubicPrice = $package->estimation_cubic_prices;
        $job = new UpdateOrCreatePriceFromExistingPackage($package, [
            'type' => Price::TYPE_SERVICE,
            'description' => Price::DESCRIPTION_TYPE_CUBIC,
            'amount' => $cubicPrice['service_fee'],
        ]);
        $this->dispatch($job);

        $servicePrice = $package->prices()->where('type', Price::TYPE_SERVICE)->where('description', Price::TYPE_SERVICE)->first();
        $servicePrice->delete();

        $totalAmount = $package->prices()->get()->sum('amount');
        $package->setAttribute('total_amount', $totalAmount)->save();
    }
}
