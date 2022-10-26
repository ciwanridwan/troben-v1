<?php

namespace App\Http\Controllers\Api\Partner\Warehouse;

use App\Http\Resources\Api\Partner\DashboardResource;
use App\Models\Packages\Item;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Models\Packages\Package;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use App\Jobs\Packages\UpdateExistingPackage;
use App\Events\Packages\WarehouseIsStartPacking;
use App\Supports\Repositories\PartnerRepository;
use App\Events\Packages\PackageEstimatedByWarehouse;
use App\Events\Packages\WarehouseIsEstimatingPackage;
use App\Events\Packages\PackageAlreadyPackedByWarehouse;
use App\Exceptions\InvalidDataException;
use App\Http\Resources\Api\Package\PackageResourceDeprecated;
use App\Http\Response;
use App\Models\Code;
use App\Models\FileUpload;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

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
            'attachments',
            'items',
            'estimator',
            'packager',
            'code.scanned_by',
            'partner_performance',
            'motoBikes'
        ]);

        return $this->jsonSuccess(PackageResourceDeprecated::collection($query->paginate($request->input('per_page', 15))), null, true);
    }

    /**
     * @param \App\Models\Packages\Package $package
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(Package $package): JsonResponse
    {
        $this->authorize('view', $package);

        return $this->jsonSuccess(PackageResourceDeprecated::make($package->load([
            'attachments',
            'items',
            'estimator',
            'packager',
        ])));
    }

    /**
     * @param \App\Models\Packages\Package $package
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function showByReceipt(Code $code): JsonResponse
    {
        if (! $code->codeable instanceof Package) {
            throw_if(true, InvalidDataException::make(Response::RC_INVALID_DATA));
        }

        $package = $code->codeable;
        $this->authorize('view', $package);

        return $this->jsonSuccess(PackageResourceDeprecated::make($package->load([
            'items',
            'estimator',
            'packager',
            'code.scanned_in',
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

        return $this->jsonSuccess(PackageResourceDeprecated::make($job->package));
    }

    public function upload(Request $request, Package $package): JsonResponse
    {
        $this->authorize('update', $package);

        $this->attributes = Validator::make($request->all(), [
            'photos' => 'required',
            'photos.*' => 'required|file',
        ])->validate();

        $files = (array) $request->file('photos');
        foreach ($files as $file) {
            $meta = [
                'title' => $file->getClientOriginalName(),
                'mime' => $file->getMimeType(),
                'ext' => $file->getClientOriginalExtension(),
            ];

            $filepath = Storage::disk('s3')->putFile('package_warehouse', $file);

            $row = [
                'package_id' => $package->getKey(),
                'created_by_id' => auth()->id(),
                'created_by_type' => 'user',
                'file' => $filepath,
                'file_type' => FileUpload::FILE_TYPE_WAREHOUSE,
                'meta' => $meta,
            ];
            FileUpload::create($row);
        }

        return $this->jsonSuccess(PackageResourceDeprecated::make($package));
    }

    public function estimating(Package $package): JsonResponse
    {
        $package->load('motoBikes');
        event(new WarehouseIsEstimatingPackage($package));

        return $this->jsonSuccess(PackageResourceDeprecated::make($package));
    }

    public function estimated(Package $package): JsonResponse
    {
        $package->load('motoBikes');
        event(new PackageEstimatedByWarehouse($package));

        return $this->jsonSuccess(PackageResourceDeprecated::make($package));
    }

    /**
     * @param \App\Models\Packages\Package $package
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function packing(Package $package): JsonResponse
    {
        event(new WarehouseIsStartPacking($package));

        return $this->jsonSuccess(PackageResourceDeprecated::make($package));
    }

    public function packed(Package $package): JsonResponse
    {
        event(new PackageAlreadyPackedByWarehouse($package));

        return $this->jsonSuccess(PackageResourceDeprecated::make($package));
    }

    /**
     * @param PartnerRepository $repository
     * @return JsonResponse
     */
    public function dashboard(PartnerRepository $repository): JsonResponse
    {
        $estimating = $repository->queries()->getPackagesQuery()
            ->whereIn('status', [Package::STATUS_WAITING_FOR_ESTIMATING, Package::STATUS_ESTIMATING])
            ->get()
            ->pluck('id');

        $estimated = $repository->queries()->getPackagesQuery()
            ->where('status', Package::STATUS_ESTIMATED)
            ->get()
            ->pluck('id');

        $packing = $repository->queries()->getPackagesQuery()
            ->whereIn('status', [Package::STATUS_WAITING_FOR_PACKING,Package::STATUS_PACKING])
            ->get()
            ->pluck('id');

        $packed = $repository->queries()->getPackagesQuery()
            ->where('status', Package::STATUS_PACKED)
            ->get()
            ->pluck('id');

        $items = $repository->queries()->getPackagesQuery()
            ->get()
            ->pluck('id');

        $returnWith = $repository->queries()->getPackagesQuery()
            ->where('status', Package::STATUS_CANCEL_DELIVERED)
            ->count();

        $returnWithout = $repository->queries()->getPackagesQuery()
            ->where('status', Package::STATUS_CANCEL_SELF_PICKUP)
            ->count();

        return $this->jsonSuccess(DashboardResource::make([
            'estimating' => $this->resolveItemsCount($estimating),
            'estimated' => $this->resolveItemsCount($estimated),
            'packing' => $this->resolveItemsCount($packing),
            'packed' => $this->resolveItemsCount($packed),
            'item_count' => $this->resolveItemsCount($items),
            'return_with_transporter' => $returnWith,
            'return_without_transporter' => $returnWithout,
        ]));
    }

    protected function resolveItemsCount($packagesId)
    {
        $item_count = 0;
        $items = Item::query()->whereIn('package_id', $packagesId)->get();

        foreach ($items as $item) {
            $item_count += $item->qty;
        }

        return $item_count;
    }
}
