<?php

namespace App\Http\Controllers\Partner\CustomerService;

use App\Concerns\Controllers\HasResource;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Delivery\DeliveryPickupResource;
use App\Http\Response;
use App\Models\Deliveries\Delivery;
use App\Models\Packages\Package;
use App\Supports\Repositories\PartnerRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

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
    /**
     * @param Request $request
     * @param PartnerRepository $partnerRepository
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\JsonResponse
     */
    public function done(Request $request, PartnerRepository $partnerRepository)
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

            $this->query = $this->query->where('status', Delivery::STATUS_FINISHED);
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

        return view('partner.customer-service.done');
    }

    /**
     * @param Request $request
     * @param PartnerRepository $partnerRepository
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\JsonResponse
     */
    public function processed(Request $request, PartnerRepository $partnerRepository)
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

            $this->getDeliveryForProcessed();
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

        return view('partner.customer-service.processed');
    }

    public function getDeliveryForProcessed(): Builder
    {
        $this->query = $this->query->where('type', Delivery::TYPE_TRANSIT)
            ->orWhere('type', Delivery::TYPE_PICKUP)
            ->Where('status','!=', Delivery::STATUS_FINISHED);
        return $this->query;
    }

    /**
     * @param Request $request
     * @param PartnerRepository $partnerRepository
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\JsonResponse
     */
    public function cancel(Request $request, PartnerRepository $partnerRepository)
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

            $this->getDeliveryForCancel();
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

        return view('partner.customer-service.cancel');
    }

    public function getDeliveryForCancel(): Builder
    {
        $this->query = $this->query->whereHas('packages', function (Builder $query) {
            $query->Where('packages.status', Package::STATUS_CANCEL_DELIVERED);
            $query->orWhere('packages.status', Package::STATUS_CANCEL_SELF_PICKUP);
            $query->orWhere('packages.status', Package::STATUS_CANCEL);
        });

        return $this->query;
    }
}
