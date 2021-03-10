<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\Error;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Package\PackageResource;
use App\Http\Response;
use App\Jobs\Packages\CreateNewPackage;
use App\Models\Customers\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {

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

        $items = $request->input('items');

        $job = new CreateNewPackage($inputs, $items);

        $this->dispatchNow($job);

        return $this->jsonSuccess(new PackageResource($job->package->load(
            'items',
            'prices',
            'origin_sub_district',
            'destination_sub_district')));
    }
}
