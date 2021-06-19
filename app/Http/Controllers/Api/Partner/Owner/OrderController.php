<?php

namespace App\Http\Controllers\Api\Partner\Owner;

use App\Http\Resources\Api\Delivery\DeliveryResource;
use App\Models\Partners\Partner;
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
}
