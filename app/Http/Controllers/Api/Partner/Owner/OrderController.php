<?php

namespace App\Http\Controllers\Api\Partner\Owner;

use App\Http\Resources\Api\Delivery\DeliveryResource;
use App\Http\Resources\Api\Partner\DashboardResource;
use App\Http\Resources\Api\Partner\VoucherResource;
use App\Http\Response;
use App\Models\Deliveries\Delivery;
use App\Models\Packages\Package;
use App\Models\Partners\Partner;
use App\Models\Partners\Voucher;
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
     * Filtered attributes.
     *
     * @var array
     */
    protected array $attributes;
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

//        $depositAmount = $repository->queries()->getPaymentQuery()
//            ->where('service_type', Payment::SERVICE_TYPE_DEPOSIT)
//            ->sum('total_payment');

        return $this->jsonSuccess(DashboardResource::make([
            'incoming_order' => $incomingOrder,
            'request_order' => $requestOrder,
            'inbound_manifest' => $inboundManifest,
            'outbound_manifest' => $outboundManifest,
            'paid_order' => $paidOrder,
            'unpaid_order' => $unpaidOrder,
            'deposit_amount' => $partner->balance,
        ]));
    }

    public function voucherList(PartnerRepository $repository): JsonResponse
    {
        $query = $this->getBasicBuilder(Voucher::query());
        $query->where('partner_id', $repository->getPartner()->id);

        return $this->jsonSuccess(VoucherResource::collection($query->paginate(request('per_page', 15))));
    }

    public function approval(PartnerRepository $repository, Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required',
            'approval' => ['required', 'boolean']
        ]);
        $voucher = Voucher::where('partner_id', $repository->getPartner()->id)
            ->where('code', $request->input('code'))
            ->first();
        $voucher->is_approved = $request->input('approval');
        $voucher->save();


        return (new Response(Response::RC_SUCCESS))->json();
    }

    private function getBasicBuilder(Builder $builder): Builder
    {
        $builder->when(request()->has('id'), fn ($q) => $q->where('id', $this->attributes['id']));
        $builder->when(
            request()->has('q') and request()->has('id') === false,
            fn ($q) => $q->where('title', 'like', '%'.$this->attributes['q'].'%')
        );

        return $builder;
    }
}
