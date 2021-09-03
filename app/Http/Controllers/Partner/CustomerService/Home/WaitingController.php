<?php

namespace App\Http\Controllers\Partner\CustomerService\Home;

use App\Concerns\Controllers\HasResource;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Delivery\DeliveryPickupResource;
use App\Http\Response;
use App\Models\Deliveries\Delivery;
use App\Models\Packages\Package;
use App\Supports\Repositories\PartnerRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class WaitingController extends Controller
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
    public function confirmation(Request $request, PartnerRepository $partnerRepository)
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

            $this->getDeliveryForConfirmation();
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

    public function getDeliveryForConfirmation(): Builder
    {
        $this->query = $this->query->whereHas('packages', function (Builder $query) {
            $query->Where('packages.status', Package::STATUS_WAITING_FOR_APPROVAL);
        });

        return $this->query;
    }

    /**
     * @param Request $request
     * @param PartnerRepository $partnerRepository
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\JsonResponse
     */
    public function payment(Request $request, PartnerRepository $partnerRepository)
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

            $this->getDeliveryForPayment();
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

    public function getDeliveryForPayment(): Builder
    {
        $this->query = $this->query->whereHas('packages', function (Builder $query) {
            $query->Where('packages.payment_status', Package::PAYMENT_STATUS_PENDING);
        });

        return $this->query;
    }
}
