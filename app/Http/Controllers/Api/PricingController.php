<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\Error;
use App\Http\Response;
use App\Models\Geo\Regency;
use App\Models\Price;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\PriceResource;
use Illuminate\Database\Eloquent\Builder;
use App\Actions\Pricing\PricingCalculator;
use App\Models\Partners\ScheduleTransportation;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class PricingController extends Controller
{
    /**
     * @var array
     */
    protected $attributes = [];

    /**
     *
     * Get Pricing List
     * Route Path       : {API_DOMAIN}/pricing
     * Route Name       : api.pricing
     * Route Method     : GET.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $this->attributes = $request->validate([
            'origin_id' => ['filled'],
            'destination_id' => ['filled'],
            'service_code' => ['filled'],
        ]);
        $prices = Price::query();

        ! Arr::has($this->attributes, 'origin_id') ?: $prices = $this->filterOrigin($prices);
        ! Arr::has($this->attributes, 'destination_id') ?: $prices = $this->filterDestination($prices);
        ! Arr::has($this->attributes, 'service_code') ?: $prices = $this->filterService($prices);

        return $this->jsonSuccess(PriceResource::collection($prices->paginate(request('per_page', 15))));
    }


    /**
     *
     * Get Pricing List
     * Route Path       : {API_DOMAIN}/pricing/calculator
     * Route Name       : api.pricing.calculator
     * Route Method     : GET.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function calculate(Request $request): JsonResponse
    {
        /** @var Regency $regency */
        $regency = Regency::query()->find($request->get('origin_regency_id'));
        $tempData = PricingCalculator::calculate(array_merge($request->toArray(), ['origin_province_id' => $regency->province_id, 'destination_id' => $request->get('destination_id')]), 'array');
        Log::info('New Order.', ['request' => $request->all(), 'tempData' => $tempData]);
        Log::info('Ordering service. ', ['result' => $tempData['result']['service'] != 0]);
        throw_if($tempData['result']['service'] == 0, Error::make(Response::RC_OUT_OF_RANGE));
        return PricingCalculator::calculate($request->toArray());
    }

    /**
     * @param Builder $query
     *
     * @return Builder
     */
    public function filterOrigin(Builder $query): Builder
    {
        $query = $query->where('origin_sub_district_id', $this->attributes['origin_id']);

        return $query;
    }
    /**
     * @param Builder $query
     *
     * @return Builder
     */
    public function filterDestination(Builder $query): Builder
    {
        $query = $query->where('destination_id', $this->attributes['destination_id']);

        return $query;
    }
    /**
     * @param Builder $query
     *
     * @return Builder
     */
    public function filterService(Builder $query): Builder
    {
        $query = $query->where('service_code', $this->attributes['service_code']);

        return $query;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function tarif(Request $request): JsonResponse
    {
        $this->attributes = $request->validate([
            'origin_id' => ['required'],
            'destination_id' => ['required'],
            'service_code' => ['required'],
        ]);
        $prices = Price::query();
        ! Arr::has($this->attributes, 'origin_id') ?: $prices = $this->filterOrigin($prices);
        ! Arr::has($this->attributes, 'destination_id') ?: $prices = $this->filterDestination($prices);
        ! Arr::has($this->attributes, 'service_code') ?: $prices = $this->filterService($prices);

        $prices = Price::where('origin_regency_id', $this->attributes['origin_id'])

            ->where('destination_id', $this->attributes['destination_id'])

            ->where('service_code', $this->attributes['service_code'])
            ->first();

        return (new Response(Response::RC_SUCCESS, $prices))->json();
    }

    // ADD SHIP SCHEDULE
    public function shipSchedule(Request $request): JsonResponse
    {
        $this->attributes = Validator::make($request->all(), [
            'origin_regency_id' => 'required',
            'destination_regency_id' => 'required',
        ])->validate();

        $schedules = ScheduleTransportation::where('origin_regency_id', $request->origin_regency_id)
            ->where('destination_regency_id', $request->destination_regency_id)
            ->orderByRaw('updated_at - created_at desc')->first();

        if ($schedules == null) {
            return (new Response(Response::RC_DATA_NOT_FOUND))->json();
        } else {
            $result = ScheduleTransportation::where('origin_regency_id', $request->origin_regency_id)
                ->where('destination_regency_id', $request->destination_regency_id)
                ->orderByRaw('departed_at desc')->limit(2)->get();

            $result->makeHidden(['created_at', 'updated_at', 'deleted_at']);
            return (new Response(Response::RC_SUCCESS, $result))->json();
        }
    }
}
