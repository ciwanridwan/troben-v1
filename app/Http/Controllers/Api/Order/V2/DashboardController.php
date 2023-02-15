<?php

namespace App\Http\Controllers\Api\Order\V2;

use App\Actions\Pricing\PricingCalculator;
use App\Casts\Package\Items\Handling;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateOrUpdateMultiRequest;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Requests\EstimationPricesRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Http\Resources\Api\Order\Dashboard\DetailResource;
use App\Http\Resources\Api\Order\Dashboard\ListDriverResource;
use App\Http\Resources\Api\Order\Dashboard\ListOrderResource;
use App\Models\Packages\Package;
use App\Models\Partners\Transporter;
use App\Models\User;
use App\Supports\Repositories\PartnerRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Response;
use App\Jobs\Deliveries\Actions\AssignDriverToDelivery;
use App\Jobs\Packages\CreateNewPackageByCs;
use App\Jobs\Packages\CustomerUploadPackagePhotos;
use App\Jobs\Packages\Item\UpdateExistingItemByCs;
use App\Jobs\Packages\UpdateExistingPackageByCs;
use App\Models\Deliveries\Delivery;
use App\Models\Packages\MultiDestination;
use App\Models\Packages\Price;
use App\Models\Partners\Pivot\UserablePivot;
use App\Services\Chatbox\Chatbox;

class DashboardController extends Controller
{
    /**
     * @var Builder
     */
    protected Builder $query;

    /**
     * Query to search.
     */
    public function getSearch(Request $request)
    {
        $this->query = $this->query->where('type', $request->type)
            ->where(function (Builder $query) use ($request) {
                $query->search($request->q, 'registration_number');
                $query->orWhereHas('drivers', fn (Builder $userQuery) => $userQuery->search($request->q, 'name'));
            });
        return $this;
    }

    public function index(PartnerRepository $partnerRepository)
    {
        $this->query = $partnerRepository->queries()->getDeliveriesQuery()->whereHas('packages')->with([
            'packages', 'packages.code',
            'packages.origin_regency',
            'packages.origin_district',
            'packages.origin_sub_district',
            'packages.destination_regency',
            'packages.destination_district',
            'packages.destination_sub_district',
            'packages.prices',
            'packages.multiDestination',
            'packages.motoBikes'
        ]);

        $this->query->where('type', Delivery::TYPE_PICKUP)->where(function ($q) {
            $q->where('status', Delivery::STATUS_PENDING)->orWhere('status', Delivery::STATUS_ACCEPTED);
        });

        $this->query->orderBy('created_at', 'desc');

        return $this->jsonSuccess(ListOrderResource::collection($this->query->paginate(request('per_page', 15))));
    }

    /** Detail order trawlpack */
    public function detail(Package $package): JsonResponse
    {
        return $this->jsonSuccess(DetailResource::make($package));
    }

    /**
     * create order
     */
    public function store(CreateOrderRequest $request): JsonResponse
    {
        $request->validated();
        $partnerCode = $request->user()->partners->first()->code;

        $package = Package::byHashOrFail($request->package_parent_hash);
        $packageExists = $package->only('customer_id', 'transporter_type', 'service_code', 'sender_address', 'sender_phone', 'sender_name', 'sender_way_point', 'origin_regency_id', 'sender_latitude', 'sender_longitude');

        $packageAttr = array_merge($packageExists, $request->except('items', 'photos', 'package_parent_hash'));

        $job = new CreateNewPackageByCs($packageAttr, $request->items, $partnerCode);
        $this->dispatchNow($job);

        $result = ['hash' => $job->package->hash];

        return (new Response(Response::RC_CREATED, $result))->json();
    }

    /** Get List Driver */
    public function listDrivers(Request $request, PartnerRepository $partnerRepository): JsonResponse
    {
        $this->query = $partnerRepository->getPartner()->transporters()->getQuery();
        $this->getSearch($request);

        $transporters = $this->query->get();
        $transporterCollections = $this->searchTransporter($transporters);

        return $this->jsonSuccess(ListDriverResource::collection($transporterCollections));
    }

    public function searchTransporter(Collection $transporters): Collection
    {
        $transporterDrivers = new Collection();

        $transporters->each(fn (Transporter $transporter) => $transporter->drivers->each(function (User $driver) use ($transporterDrivers, $transporter) {
            $transporterSelected = $transporter->only('type', 'registration_number');
            $driverSelected = $driver->only('name');
            $userableSelected = $driver->only('pivot');
            $hashUserable = ['hash' => $userableSelected['pivot']->hash];
            $driverArr = array_merge($driverSelected, $hashUserable);

            $transporter->unsetRelation('drivers');
            $transporterDriver = array_merge($transporterSelected, ['driver' => $driverArr]);
            $transporterDriver = new Collection($transporterDriver);
            $transporterDrivers->push($transporterDriver);
        }));
        return $transporterDrivers;
    }

    public function orderAssignation(Delivery $delivery, UserablePivot $userablePivot): JsonResponse
    {
        $method = 'partner';
        $job = new AssignDriverToDelivery($delivery, $userablePivot, $method);
        $this->dispatchNow($job);
        $driverSignIn = User::where('id', $job->delivery->assigned_to->user_id)->first();
        if ($driverSignIn) {
            $token = auth('api')->login($driverSignIn);
        }
        $param = [
            'token' => $token ?? null,
            'type' => 'trawlpack',
            'participant_id' => $job->delivery->assigned_to->user_id,
            'customer_id' => $delivery->packages[0]->customer_id,
            'package_id' => $job->delivery->packages[0]->id,
            'product' => 'trawlpack'
        ];

        try {
            Chatbox::createDriverChatbox($param);
        } catch (\Exception $e) {
            report($e);
        }
        return (new Response(Response::RC_SUCCESS, ['Message' => 'Driver berhasil di assign']))->json();
    }

    public function update(UpdateOrderRequest $request, Package $package): JsonResponse
    {
        $request->validated();

        $job = new UpdateExistingPackageByCs($package, $request->all());
        $this->dispatchNow($job);

        $job = new UpdateExistingItemByCs($package, $request->all());
        $this->dispatchNow($job);

        if ($request->photos) {
            $package->attachments()->detach();
            $uploadJob = new CustomerUploadPackagePhotos($package, $request->file('photos') ?? []);
            $this->dispatchNow($uploadJob);
        }

        return (new Response(Response::RC_UPDATED, ['message' => 'Update data successfully']))->json();
    }

    /**
     * Like a pricing calculator
     * This is estimation price when to create order.
     */
    public function estimationPrices(EstimationPricesRequest $request): JsonResponse
    {
        $request->validated();

        // if items is delete
        if ($request->items === []) {
            $zeroResult = [
                'service_fee' => 0,
                'insurance_fee' => 0,
                'handling_fee' => 0,
                'additional_fee' => 0,
                'total_amount' => 0
            ];

            return (new Response(Response::RC_SUCCESS, $zeroResult))->json();
        }

        $package = Package::byHashOrFail($request->package_hash);

        $provinceId = $package->origin_regency ? $package->origin_regency->province->id : null;
        $originLocation = ['origin_province_id' => $provinceId, 'origin_regency_id' => $package->origin_regency_id];

        $servicePrice = PricingCalculator::getServicePrice(array_merge($request->all(), $originLocation));

        $additionalPrice = PricingCalculator::getAdditionalPrices($request->items, $package->service_code);

        $items = $request->items;

        $handlingPrice = 0;

        $totalInsurance = [];
        foreach ($items as $item) {
            // insurance
            $totalItem = PricingCalculator::getInsurancePrice($item['price'] * $item['qty']);
            array_push($totalInsurance, $totalItem);

            // handling or packing
            foreach ($item['handling'] as $packing) {
                $handlingPrice = Handling::calculator($packing['type'], $item['height'], $item['length'], $item['width'], $item['weight']);
            }
        }
        $insurancePrice = array_sum($totalInsurance);
        $totalAmount = $servicePrice + $insurancePrice + $handlingPrice + $additionalPrice;
        $result = [
            'service_fee' => $servicePrice,
            'insurance_fee' => $insurancePrice,
            'handling_fee' => $handlingPrice,
            'additional_fee' => $additionalPrice,
            'total_amount' => $totalAmount
        ];

        return (new Response(Response::RC_SUCCESS, $result))->json();
    }

    /**
     * create or update multi destination package (order)
     */
    public function createOrUpdateMulti(CreateOrUpdateMultiRequest $request): JsonResponse
    {
        if (is_null($request->package_parent_hash) && is_null($request->package_child_hash)) {
            return (new Response(Response::RC_SUCCESS))->json();
        } else {
            $parentPackage = Package::hashToId($request->package_parent_hash);
            $childPackage = $request->package_child_hash;
            $childIds = [];

            $parentReceipt = Package::byHashOrFail($request->package_parent_hash);

            if (count($parentReceipt->multiDestination) !== 0) {
                for ($i = 0; $i < count($childPackage); $i++) {
                    $childId = Package::hashToId($childPackage[$i]);
                    array_push($childIds, $childId);

                    $parentReceipt->multiDestination()->create([
                        'child_id' => $childId
                    ]);
                    dd($parentReceipt);
                }
            } else {
                for ($i = 0; $i < count($childPackage); $i++) {
                    $childId = Package::hashToId($childPackage[$i]);
                    array_push($childIds, $childId);

                    MultiDestination::create([
                        'parent_id' => $parentPackage,
                        'child_id' => $childId
                    ]);
                }
            }

            $packageChild = Package::whereIn('id', $childIds)->get();
            $packageChild->each(function ($q) {
                $pickupFee = $q->prices->where('type', Price::TYPE_DELIVERY)->where('description', Price::TYPE_PICKUP)->first();

                $q->total_amount -= $pickupFee->amount;
                $q->save();

                $pickupFee->amount = 0;
                $pickupFee->save();
            });

            return (new Response(Response::RC_SUCCESS, ['Message' => 'Create Multi Destination Order Has Successfully']))->json();
        }
    }
}
