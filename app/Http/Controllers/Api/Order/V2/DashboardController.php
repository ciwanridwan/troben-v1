<?php

namespace App\Http\Controllers\Api\Order\V2;

use App\Actions\Pricing\PricingCalculator;
use App\Exceptions\Error;
use App\Exceptions\OutOfRangePricingException;
use App\Http\Controllers\Controller;
use App\Http\Requests\EstimationPricesRequest;
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
use App\Models\Deliveries\Delivery;
use App\Models\Partners\Partner;
use App\Models\Partners\Pivot\UserablePivot;
use App\Services\Chatbox\Chatbox;
use Veelasky\LaravelHashId\Rules\ExistsByHash;

class DashboardController extends Controller
{
    /**
     * @var Builder
     */
    protected Builder $query;

    /**
     * Query to search
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

    public function update()
    {

    }

    public function estimationPrices(EstimationPricesRequest $request): JsonResponse
    {
        $request->validated();

        $package = Package::byHashOrFail($request->package_hash);

        $isAdmin = auth()->user()->is_admin;
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
}
