<?php

namespace App\Http\Controllers\Api;

use App\Http\Response;
use App\Exceptions\Error;
use App\Models\Packages\Package;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Customers\Customer;
use App\Http\Controllers\Controller;
use App\Jobs\Packages\CreateNewPackage;
use App\Http\Resources\Api\Package\PackageResource;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderController extends Controller
{
    public function index(Request $request): LengthAwarePaginator
    {
        $query = Package::query();

        $query->when($request->input('order'),
            fn(Builder $query, string $order) => $query->orderBy($order, $request->input('order_direction', 'asc')),
            fn(Builder $query) => $query->orderByDesc('created_at'));

        return $query->paginate();
    }

    public function show(Package $package): JsonResponse
    {
        return $this->jsonSuccess(new JsonResource($package->load(['items'])));
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
            'origin_sub_district',
            'destination_sub_district')));
    }
}
