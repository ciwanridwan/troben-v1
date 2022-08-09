<?php

namespace App\Http\Controllers\Api\Order;

use App\Actions\Pricing\PricingCalculator;
use App\Http\Controllers\Controller;
use App\Http\Response;
use App\Models\Customers\Customer;
use App\Models\Geo\Regency;
use App\Exceptions\Error;
use App\Http\Resources\Api\Package\PackageResource;
use App\Jobs\Packages\CreateMotorBike;
use App\Jobs\Packages\CreateNewPackage;
use App\Jobs\Packages\CustomerUploadPackagePhotos;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MotorBikeController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'items' => ['required'],
            'items.*.is_insured' => ['nullable'],
            'photos' => ['nullable'],
            'photos.*' => ['nullable', 'image'],
        ]);

        $inputs = $request->except('items');

        /** @var Customer $user */
        $user = $request->user();

        /** @noinspection PhpParamsInspection */
        /** @noinspection PhpUnhandledExceptionInspection */
        throw_if(! $user instanceof Customer, Error::class, Response::RC_UNAUTHORIZED);
        /** @var Regency $regency */
        $regency = Regency::query()->find($request->get('origin_regency_id'));
        // dd($regency);
        $tempData = PricingCalculator::calculate(array_merge($request->toArray(), ['origin_province_id' => $regency->province_id, 'destination_id' => $request->get('destination_sub_district_id')]), 'array');
        Log::info('New Order.', ['request' => $request->all(), 'tempData' => $tempData]);
        Log::info('Ordering service. ', ['result' => $tempData['result']['service'] != 0]);
        throw_if($tempData['result']['service'] == 0, Error::make(Response::RC_OUT_OF_RANGE));

        $inputs['customer_id'] = $user->id;

        $items = $request->input('items') ?? [];

        foreach ($items as $key => $item) {
            if ($item['insurance'] == '1') {
                $items[$key]['is_insured'] = true;
            }
        }
        // $job = new CreateNewPackage($inputs, $items);
        $job = new CreateMotorBike($inputs, $items);

        $this->dispatchNow($job);
        Log::info('after dispatch job. ', [$request->get('sender_name')]);

        $uploadJob = new CustomerUploadPackagePhotos($job->package, $request->file('photos') ?? []);

        $this->dispatchNow($uploadJob);

        return $this->jsonSuccess(new PackageResource($job->package->load(
            'items',
            'prices',
            'origin_regency',
            'destination_regency',
            'destination_district',
            'destination_sub_district'
        )));
    }
}
