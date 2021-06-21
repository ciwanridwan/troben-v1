<?php

namespace App\Http\Controllers\Partner\CustomerService\Home;

use App\Concerns\Controllers\HasResource;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Delivery\DeliveryPickupResource;
use App\Http\Response;
use App\Jobs\Deliveries\Actions\AssignDriverToDelivery;
use App\Jobs\Deliveries\Actions\RejectDeliveryFromPartner;
use App\Models\Deliveries\Delivery;
use App\Models\Partners\Pivot\UserablePivot;
use App\Models\Partners\Transporter;
use App\Models\User;
use App\Supports\Repositories\PartnerRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

        $transporters->each(fn (Transporter $transporter) => $transporter->users->each(function (User $driver) use ($transporterDrivers, $transporter) {
            $transporter->unsetRelation('users');
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

    public function orderAssignation(Delivery $delivery, UserablePivot $userablePivot): JsonResponse
    {
        $job = new AssignDriverToDelivery($delivery, $userablePivot);
        $this->dispatchNow($job);

        return (new Response(Response::RC_SUCCESS, $job->delivery))->json();
    }
    public function getSearch(Request $request)
    {
        $this->query = $this->query->where('type', $request->type)
            ->with('users')
            ->where(function (Builder $query) use ($request) {
                $query->search($request->q, 'registration_number');
                $query->orWhereHas('users', fn (Builder $userQuery) => $userQuery->search($request->q, 'name'));
            });
        return $this;
    }

    public function getDeliveryForAssignDriver(): Builder
    {
        $this->query = $this->query->where('type', Delivery::TYPE_PICKUP)->where('status', Delivery::STATUS_PENDING);
        return $this->query;
    }
}
