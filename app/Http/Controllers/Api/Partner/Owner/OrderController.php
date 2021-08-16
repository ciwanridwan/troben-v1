<?php

namespace App\Http\Controllers\Api\Partner\Owner;

use App\Http\Resources\Api\Delivery\DeliveryResource;
use App\Http\Resources\Api\Partner\DashboardResource;
use App\Models\Deliveries\Delivery;
use App\Models\Packages\Package;
use App\Models\Partners\Partner;
use App\Models\Payments\Payment;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use App\Supports\Repositories\PartnerRepository;
use App\Http\Resources\Api\Package\PackageResource;

class OrderController extends Controller
{
    /** @var Builder $query */
    protected Builder $query;

    /**
     * @param Request $request
     * @param PartnerRepository $repository
     * @return JsonResponse
     */
    public function index(Request $request, PartnerRepository $repository): JsonResponse
    {
        if ($repository->getPartner()->type === Partner::TYPE_TRANSPORTER) {
            $this->query = $repository->queries()->getDeliveriesByUserableQuery();
            return $this->orderByDeliveries($request);
        } else {
            $this->query = $repository->queries()->getPackagesQuery();
            return $this->orderByPackages($request);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function orderByDeliveries(Request $request): JsonResponse
    {
        $this->query->when($request->input('status'), fn (Builder $builder, $status) => $builder->whereIn('status', Arr::wrap($status)));

        return $this->jsonSuccess(DeliveryResource::collection($this->query->paginate($request->input('per_page'))));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function orderByPackages(Request $request): JsonResponse
    {
        $this->query->when($request->input('status'), fn (Builder $builder, $status) => $builder->whereIn('status', Arr::wrap($status)));

        $this->query->when($request->input('delivery_type'), fn (Builder $builder, $deliveryType) => $builder
            ->whereHas('deliveries', fn (Builder $builder) => $builder
                ->whereIn('type', Arr::wrap($deliveryType))));

        $this->query->with(['items', 'items.prices', 'estimator', 'packager']);
        return $this->jsonSuccess(PackageResource::collection($this->query->paginate($request->input('per_page'))));
    }

    /**
     * @param PartnerRepository $repository
     * @return JsonResponse
     */
    public function dashboard(PartnerRepository $repository): JsonResponse
    {
        $partner = $repository->getPartner();
        $incomingOrder = $repository->getPartner()->deliveries()->where('type', Delivery::TYPE_PICKUP)
            ->where('status', Delivery::STATUS_ACCEPTED)
            ->count();

        $requestOrder = $repository->getPartner()->deliveries()->where('type', Delivery::TYPE_PICKUP)
            ->where('status', Delivery::STATUS_PENDING)
            ->count();

        $inboundManifest = $repository->getPartner()->deliveries()
            ->where('type', Delivery::TYPE_TRANSIT)
            ->where('partner_id', $partner->id)
            ->count();

        $outboundManifest = $repository->getPartner()->outbound()
            ->where('type', Delivery::TYPE_TRANSIT)
            ->where('status', '!=', Delivery::STATUS_FINISHED)
            ->count();

        $paidOrder = $repository->queries()->getPackagesQuery()
            ->where('payment_status', Package::PAYMENT_STATUS_PAID)
            ->count();

        $unpaidOrder = $repository->queries()->getPackagesQuery()
            ->whereIn('payment_status', [Package::PAYMENT_STATUS_PENDING, Package::PAYMENT_STATUS_DRAFT])
            ->count();

        $depositAmount = $repository->queries()->getPaymentQuery()
            ->where('service_type', Payment::SERVICE_TYPE_DEPOSIT)
            ->sum('total_payment');

        return $this->jsonSuccess(DashboardResource::make([
            'incoming_order' => $incomingOrder,
            'request_order' => $requestOrder,
            'inbound_manifest' => $inboundManifest,
            'outbound_manifest' => $outboundManifest,
            'paid_order' => $paidOrder,
            'unpaid_order' => $unpaidOrder,
            'deposit_amount' => (float) $depositAmount,
        ]));
    }
}
