<?php

namespace App\Http\Controllers\Api\Order;

use App\Http\Response;
use App\Exceptions\Error;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Models\Packages\Package;
use Illuminate\Http\JsonResponse;
use App\Models\Customers\Customer;
use App\Http\Controllers\Controller;
use App\Jobs\Packages\CreateNewPackage;
use Illuminate\Database\Eloquent\Builder;
use App\Jobs\Packages\CustomerUploadReceipt;
use App\Jobs\Packages\UpdateExistingPackage;
use App\Events\Packages\PackageApprovedByCustomer;
use App\Http\Resources\Api\Package\PackageResource;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = $request->user()->packages();

        $query->when(
            $request->input('order'),
            fn (Builder $query, string $order) => $query->orderBy($order, $request->input('order_direction', 'asc')),
            fn (Builder $query) => $query->orderByDesc('created_at')
        );

        $query->when($request->input('status'), fn (Builder $builder, $status) => $builder->whereIn('status', Arr::wrap($status)));

        $query->with('origin_regency', 'destination_regency', 'destination_district', 'destination_sub_district');

        $paginate = $query->paginate();

        return $this->jsonSuccess(PackageResource::collection($paginate));
    }

    /**
     * @param \App\Models\Packages\Package $package
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(Package $package): JsonResponse
    {
        $this->authorize('view', $package);

        return $this->jsonSuccess(new PackageResource($package->load(
            'attachments',
            'items',
            'deliveries.partner',
            'deliveries.assigned_to.userable',
            'deliveries.assigned_to.user',
            'prices',
            'origin_regency',
            'destination_regency',
            'destination_district',
            'destination_sub_district'
        )));
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Throwable
     */
    public function store(Request $request): JsonResponse
    {
        $inputs = $request->except('items');

        /** @var Customer $user */
        $user = $request->user();

        /** @noinspection PhpParamsInspection */
        /** @noinspection PhpUnhandledExceptionInspection */
        throw_if(! $user instanceof Customer, Error::class, Response::RC_UNAUTHORIZED);

        $inputs['customer_id'] = $user->id;

        $items = $request->input('items') ?? [];

        $job = new CreateNewPackage($inputs, $items);

        $this->dispatchNow($job);

        return $this->jsonSuccess(new PackageResource($job->package->load(
            'items',
            'prices',
            'origin_regency',
            'destination_regency',
            'destination_district',
            'destination_sub_district'
        )));
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Packages\Package $package
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Throwable
     */
    public function update(Request $request, Package $package): JsonResponse
    {
        $this->authorize('update', $package);

        $inputs = $request->all();

        /** @var Customer $user */
        $user = $request->user();

        /** @noinspection PhpParamsInspection */
        /** @noinspection PhpUnhandledExceptionInspection */
        throw_if(! $user instanceof Customer, Error::class, Response::RC_UNAUTHORIZED);

        $job = new UpdateExistingPackage($package, $inputs);

        $this->dispatchNow($job);

        return $this->jsonSuccess(new PackageResource($job->package->load(
            'prices',
            'origin_regency',
            'destination_regency',
            'destination_district',
            'destination_sub_district'
        )));
    }

    /**
     * @param \App\Models\Packages\Package $package
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException|\Throwable
     */
    public function approve(Package $package): JsonResponse
    {
        $this->authorize('update', $package);

        event(new PackageApprovedByCustomer($package));

        return $this->jsonSuccess(PackageResource::make($package->fresh()));
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Packages\Package $package
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function receipt(Request $request, Package $package): JsonResponse
    {
        $this->authorize('update', $package);

        $request->validate([
            'receipt' => 'required|image',
        ]);

        $job = new CustomerUploadReceipt($package, $request->file('receipt'));

        $this->dispatchNow($job);

        return $this->jsonSuccess(PackageResource::make($job->package->load('attachments')));
    }
}
