<?php

namespace App\Http\Controllers\Api;

use App\Http\Response;
use App\Exceptions\Error;
use Illuminate\Http\Request;
use App\Models\Packages\Package;
use Illuminate\Http\JsonResponse;
use App\Models\Customers\Customer;
use App\Http\Controllers\Controller;
use App\Jobs\Packages\CreateNewPackage;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Resources\Api\Package\PackageResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrderController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Package::query();

        $query->when($request->input('order'),
            fn (Builder $query, string $order) => $query->orderBy($order, $request->input('order_direction', 'asc')),
            fn (Builder $query) => $query->orderByDesc('created_at'));

        $query->with('origin_regency', 'destination_regency', 'destination_district', 'destination_sub_district');

        $paginate = $query->paginate();

        return PackageResource::collection($paginate);
    }

    public function show(Package $package): JsonResponse
    {
        return $this->jsonSuccess(new PackageResource($package->load(
            'items',
            'prices',
            'origin_regency',
            'destination_regency',
            'destination_district',
            'destination_sub_district')));
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
            'destination_sub_district')));
    }
}
