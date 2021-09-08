<?php

namespace App\Http\Controllers\Partner\CustomerService\Home;

use App\Concerns\Controllers\HasResource;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Delivery\DeliveryPassedResource;
use App\Http\Resources\Api\Delivery\DeliveryPickupResource;
use App\Http\Response;
use App\Jobs\Deliveries\Actions\AssignDriverToDelivery;
use App\Jobs\Deliveries\Actions\RejectDeliveryFromPartner;
use App\Models\Deliveries\Delivery;
use App\Models\HistoryReject;
use App\Models\Packages\Package;
use App\Models\Partners\Pivot\UserablePivot;
use App\Models\Partners\Transporter;
use App\Models\User;
use App\Supports\Repositories\PartnerRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
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

    /**
     * @param Request $request
     * @param PartnerRepository $partnerRepository
     * @return Application|Factory|View|JsonResponse
     */
    public function pickup(Request $request, PartnerRepository $partnerRepository)
    {
        if ($request->expectsJson()) {
            $this->query = $partnerRepository->queries()->getDeliveriesQuery()->whereHas('packages')->with([
                'packages', 'packages.code',
                'packages.origin_regency',
                'packages.origin_district',
                'packages.origin_sub_district',
                'packages.destination_regency',
                'packages.destination_district',
                'packages.destination_sub_district',
            ]);

            $this->getDeliveryForAssignDriver();

            $this->attributes = $request->validate($this->rules);

            if ($request->has('transporter')) {
                return $this->getTransporterList($request, $partnerRepository);
            } else {
                $this->query = $this->query->where(function (Builder $query) use ($request) {
                    $query->whereHas('packages', function (Builder $query) use ($request) {
                        $query->search($request->q);
                    })->orWhereHas('packages.code', function (Builder $query) use ($request) {
                        $query->search($request->q);
                    });
                });

                /** @var Delivery $deliveries */
                $deliveries = $this->query->paginate(request('per_page', 15));
                $deliveryCollection = $deliveries->getCollection();
                $deliveryCollection = DeliveryPickupResource::collection($deliveryCollection);

                $deliveries->setCollection($deliveryCollection->collection);

                return (new Response(Response::RC_SUCCESS, $deliveries))->json();
            }
        }

        return view('partner.customer-service.home.index');
    }

    public function getTransporterList(Request $request, PartnerRepository $partnerRepository): JsonResponse
    {
        $this->query = $partnerRepository->getPartner()->transporters()->getQuery();

        $this->getSearch($request);

        $transporters = $this->query->paginate(request('per_page', 15));
        $transporterCollections = $transporters->getCollection();
        $transporterCollections = $this->searchTransporter($transporterCollections);
        $transporters->setCollection($transporterCollections);

        return (new Response(Response::RC_SUCCESS, $transporterCollections))->json();
    }

    public function searchTransporter(Collection $transporters): Collection
    {
        $transporterDrivers = new Collection();

        $transporters->each(fn (Transporter $transporter) => $transporter->drivers->each(function (User $driver) use ($transporterDrivers, $transporter) {
            $transporter->unsetRelation('drivers');
            $transporterDriver = array_merge($transporter->toArray(), ['driver' => $driver->toArray()]);
            $transporterDriver = new Collection($transporterDriver);
            $transporterDrivers->push($transporterDriver);
        }));
        return $transporterDrivers;
    }

    public function orderReject(Delivery $delivery, PartnerRepository $partnerRepository): JsonResponse
    {
        $partner = $partnerRepository->getPartner();
        $job = new RejectDeliveryFromPartner($delivery, $partner);
        $this->dispatchNow($job);

        return (new Response(Response::RC_SUCCESS, $job->delivery))->json();
    }

    public function orderAssignation(Delivery $delivery, UserablePivot $userablePivot ): JsonResponse
    {
        $method = 'driver';
        $job = new AssignDriverToDelivery($delivery, $userablePivot, $method);
        $this->dispatchNow($job);

        return (new Response(Response::RC_SUCCESS, $job->delivery))->json();
    }

    public function courierAssignation(Delivery $delivery): JsonResponse
    {
        $user = $delivery->packages->first();
        $data = User::query()
            ->select('users.*', DB::raw('6371 * acos(cos(radians('.$user->sender_latitude.'))
        * cos(radians(users.latitude))
        * cos(radians(users.longitude) - radians('.$user->sender_longitude.'))
        + sin(radians('.$user->sender_latitude.'))
        * sin(radians(users.latitude))) AS distance'))
            ->groupBy('users.id')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->orderby('distance')
            ->first();
        $userablePivot = UserablePivot::where('user_id', '=', $data->id)->firstOrFail();
        $method = 'independent';

        $job = new AssignDriverToDelivery($delivery, $userablePivot, $method);
        $this->dispatchNow($job);

        return (new Response(Response::RC_SUCCESS, $job->delivery))->json();
    }

    public function getSearch(Request $request)
    {
        $this->query = $this->query->where('type', $request->type)
            ->where(function (Builder $query) use ($request) {
                $query->search($request->q, 'registration_number');
                $query->orWhereHas('drivers', fn (Builder $userQuery) => $userQuery->search($request->q, 'name'));
            });
        return $this;
    }

    public function getDeliveryForAssignDriver(): Builder
    {
        $this->query = $this->query->where('type', Delivery::TYPE_PICKUP)->where('status', Delivery::STATUS_PENDING);
        return $this->query;
    }

    /**
     * @param Request $request
     * @param PartnerRepository $partnerRepository
     * @return Application|Factory|View|JsonResponse
     */
    public function passed(Request $request, PartnerRepository $partnerRepository)
    {
        if ($request->expectsJson()) {
            $this->query = $partnerRepository->queries()->getHistoryRejectedQuery()->whereHas('packages')->with([
                'packages',
                'packages.code',
                'packages.origin_regency',
                'packages.origin_district',
                'packages.origin_sub_district',
                'packages.destination_regency',
                'packages.destination_district',
                'packages.destination_sub_district',
            ]);

            $this->getDeliveryForPassed();
            $this->attributes = $request->validate($this->rules);

            $this->query = $this->query->where(function (Builder $query) use ($request) {
                $query->whereHas('packages', function (Builder $query) use ($request) {
                    $query->search($request->q);
                })->orWhereHas('packages.code', function (Builder $query) use ($request) {
                    $query->search($request->q);
                });
            });

            /** @var HistoryReject $deliveries */
            $deliveries = $this->query->paginate(request('per_page', 15));
            $deliveryCollection = $deliveries->getCollection();
            $deliveryCollection = DeliveryPassedResource::collection($deliveryCollection);
            $deliveries->setCollection($deliveryCollection->collection);
            return (new Response(Response::RC_SUCCESS, $deliveries))->json();
        }

        return view('partner.customer-service.order.passed.index');
    }

    /**
     * @param Request $request
     * @param PartnerRepository $partnerRepository
     * @return Application|Factory|View|JsonResponse
     */
    public function taken(Request $request, PartnerRepository $partnerRepository)
    {
        if ($request->expectsJson()) {
            $this->query = $partnerRepository->queries()->getDeliveriesQuery()->whereHas('packages')->with([
                'packages', 'packages.code',
                'packages.origin_regency',
                'packages.origin_district',
                'packages.origin_sub_district',
                'packages.destination_regency',
                'packages.destination_district',
                'packages.destination_sub_district',
            ]);

            $this->getDeliveryForTaken();
            $this->attributes = $request->validate($this->rules);

            if ($request->has('transporter')) {
                return $this->getTransporterList($request, $partnerRepository);
            } else {
                $this->query = $this->query->where(function (Builder $query) use ($request) {
                    $query->whereHas('packages', function (Builder $query) use ($request) {
                        $query->search($request->q);
                    })->orWhereHas('packages.code', function (Builder $query) use ($request) {
                        $query->search($request->q);
                    });
                });

                /** @var Delivery $deliveries */
                $deliveries = $this->query->paginate(request('per_page', 15));
                $deliveryCollection = $deliveries->getCollection();
                $deliveryCollection = DeliveryPickupResource::collection($deliveryCollection);

                $deliveries->setCollection($deliveryCollection->collection);

                return (new Response(Response::RC_SUCCESS, $deliveries))->json();
            }
        }

        return view('partner.customer-service.order.taken.index');
    }

    public function getDeliveryForPassed(): Builder
    {
        $this->query = $this->query->where('status', HistoryReject::STATUS_REJECTED);
        return $this->query;
    }

    public function getDeliveryForTaken(): Builder
    {
        $this->query = $this->query->where('type', Delivery::TYPE_TRANSIT)
            ->where('status', Delivery::STATUS_EN_ROUTE)
            ->orWhere('type', Delivery::TYPE_PICKUP)
            ->orWhere('status', Delivery::STATUS_ACCEPTED);
        return $this->query;
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
