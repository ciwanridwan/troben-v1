<?php

namespace App\Http\Controllers\Api\Partner\Warehouse;

use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Models\Packages\Package;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use App\Jobs\Packages\UpdateExistingPackage;
use App\Supports\Repositories\PartnerRepository;
use App\Http\Resources\Api\Package\PackageResource;
use App\Events\Packages\PackageEstimatedByWarehouse;
use App\Events\Packages\WarehouseIsEstimatingPackage;

class OrderController extends Controller
{
    public function index(Request $request, PartnerRepository $repository): JsonResponse
    {
        $query = $repository->queries()->getPackagesQuery();

        $query->when($request->input('status'), fn (Builder $builder, $status) => $builder
            ->whereIn('status', Arr::wrap($status)));

        $query->when($request->input('delivery_type'), fn (Builder $builder, $deliveryType) => $builder
            ->whereHas('deliveries', fn (Builder $builder) => $builder
                ->whereIn('type', Arr::wrap($deliveryType))));

        $query->with([
            'estimator',
            'packager',
        ]);

        return $this->jsonSuccess(PackageResource::collection($query->paginate($request->input('per_page', 15))));
    }

    /**
     * @param \App\Models\Packages\Package $package
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(Package $package): JsonResponse
    {
        $this->authorize('view', $package);

        return $this->jsonSuccess(PackageResource::make($package->load([
            'items',
            'estimator',
            'packager',
        ])));
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Packages\Package $package
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, Package $package): JsonResponse
    {
        $this->authorize('update', $package);

        $job = new UpdateExistingPackage($package, $request->all());

        $this->dispatchNow($job);

        return $this->jsonSuccess(PackageResource::make($job->package));
    }

    public function estimating(Package $package): JsonResponse
    {
        event(new WarehouseIsEstimatingPackage($package));

        return $this->jsonSuccess(PackageResource::make($package));
    }

    public function estimated(Package $package): JsonResponse
    {
        event(new PackageEstimatedByWarehouse($package));

        return $this->jsonSuccess(PackageResource::make($package));
    }
}
