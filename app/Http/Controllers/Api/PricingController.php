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
use App\Http\Resources\Api\Pricings\CheckPriceResource;
use App\Models\Packages\CubicPrice;
use App\Models\Packages\ExpressPrice;
use App\Models\Partners\ScheduleTransportation;
use App\Models\Service;
use App\Supports\Geo;
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

        !Arr::has($this->attributes, 'origin_id') ?: $prices = $this->filterOrigin($prices);
        !Arr::has($this->attributes, 'destination_id') ?: $prices = $this->filterDestination($prices);
        !Arr::has($this->attributes, 'service_code') ?: $prices = $this->filterService($prices);

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
        $origin_regency_id = $request->get('origin_regency_id');
        $destination_id = $request->get('destination_id');
        if ($origin_regency_id == null) {
            // add validation
            $request->validate([
                'origin_lat' => 'required|numeric',
                'origin_lon' => 'required|numeric',
            ]);

            $coordOrigin = sprintf('%s,%s', $request->get('origin_lat'), $request->get('origin_lon'));
            $resultOrigin = Geo::getRegional($coordOrigin, true);
            if ($resultOrigin == null) {
                throw Error::make(Response::RC_INVALID_DATA, ['message' => 'Origin not found', 'coord' => $coordOrigin]);
            }

            $origin_regency_id = $resultOrigin['regency'];
            $request->merge([
                'origin_regency_id' => $origin_regency_id,
                'destination_id' => $destination_id,
                'sender_latitude' => $request->get('origin_lat'),
                'sender_longitude' => $request->get('origin_lon'),
            ]);
        }

        /** @var Regency $regency */
        $regency = Regency::query()->findOrFail($origin_regency_id);
        $additional = ['origin_province_id' => $regency->province_id, 'origin_regency_id' => $origin_regency_id, 'destination_id' => $destination_id];
        $payload = array_merge($request->toArray(), $additional);
        $tempData = PricingCalculator::calculate($payload, 'array');
        Log::info('New Order.', ['request' => $request->all(), 'tempData' => $tempData]);
        Log::info('Ordering service. ', ['result' => $tempData['result']['service'] != 0]);
        throw_if($tempData['result']['service'] == 0, Error::make(Response::RC_OUT_OF_RANGE));
        return PricingCalculator::calculate($payload);
    }

    /**
     *
     * Get Pricing Location
     * Route Path       : {API_DOMAIN}/pricing/location
     * Route Name       : api.pricing.location
     * Route Method     : GET.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function locationCheck(Request $request): JsonResponse
    {
        $request->validate([
            'location_lat' => 'required|numeric',
            'location_lon' => 'required|numeric',
        ]);

        $coordLocation = sprintf('%s,%s', $request->get('location_lat'), $request->get('location_lon'));
        $resultLocation = Geo::getRegional($coordLocation);
        if ($resultLocation == null) {
            throw Error::make(Response::RC_INVALID_DATA, ['message' => 'Location not found', 'coord' => $coordLocation]);
        }

        return (new Response(Response::RC_SUCCESS, $resultLocation))->json();
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
            'origin_id' => ['nullable', 'numeric', 'exists:geo_regencies,id'],
            'destination_id' => ['nullable', 'numeric', 'exists:geo_sub_districts,id'],
            // 'service_code' => ['nullable', 'exists:services,code'],
        ]);

        $originId = $this->attributes['origin_id'];
        $destinationId = $this->attributes['destination_id'];

        return $this->getAllPrices($originId, $destinationId);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * Add Ship Schedule
     */
    public function shipSchedule(Request $request): JsonResponse
    {
        $origin_regency_id = $request->get('origin_regency_id');
        $destination_regency_id = $request->get('destination_regency_id');
        if ($origin_regency_id == null || $destination_regency_id == null) {
            // add validation
            $request->validate([
                'origin_lat' => 'required|numeric',
                'origin_lon' => 'required|numeric',
                'destination_lat' => 'required|numeric',
                'destination_lon' => 'required|numeric',
            ]);

            $coordOrigin = sprintf('%s,%s', $request->get('origin_lat'), $request->get('origin_lon'));
            $resultOrigin = Geo::getRegional($coordOrigin);
            if ($resultOrigin == null) {
                throw Error::make(Response::RC_INVALID_DATA, ['message' => 'Origin not found', 'coord' => $coordOrigin]);
            }

            $coordDestination = sprintf('%s,%s', $request->get('destination_lat'), $request->get('destination_lon'));
            $resultDestination = Geo::getRegional($coordDestination);
            if ($resultDestination == null) {
                throw Error::make(Response::RC_INVALID_DATA, ['message' => 'Destination not found', 'coord' => $coordDestination]);
            }

            $origin_regency_id = $resultOrigin['regency'];
            $destination_regency_id = $resultDestination['regency'];
        } else {
            $request->validate([
                'origin_regency_id' => 'required|numeric',
                'destination_regency_id' => 'required|numeric',
            ]);
        }

        $schedules = ScheduleTransportation::where('origin_regency_id', $origin_regency_id)
            ->where('destination_regency_id', $destination_regency_id)
            ->orderByRaw('updated_at - created_at desc')->first();

        if ($schedules == null) {
            return (new Response(Response::RC_DATA_NOT_FOUND))->json();
        } else {
            $result = ScheduleTransportation::where('origin_regency_id', $origin_regency_id)
                ->where('destination_regency_id', $destination_regency_id)
                ->orderByRaw('departed_at asc')->get();

            $result->makeHidden(['created_at', 'updated_at', 'deleted_at', 'harbor_id']);
            return (new Response(Response::RC_SUCCESS, $result))->json();
        }
    }

    private function getAllPrices($originId, $destinationId)
    {
        $regularPrices = Price::where('origin_regency_id', $originId)
            ->where('destination_id', $destinationId)
            ->where('service_code', Service::TRAWLPACK_STANDARD)
            ->first();

        if ($regularPrices !== null) {
            $regularPrices = $regularPrices->only('tier_1', 'notes');

            $resultPrices['amount'] = $regularPrices['tier_1'];
            $resultPrices['notes'] = $regularPrices['notes'];
        }

        $cubicPrices = CubicPrice::where('origin_regency_id', $originId)
            ->where('destination_id', $destinationId)
            ->where('service_code', Service::TRAWLPACK_CUBIC)
            ->first();

        if ($cubicPrices !== null) {
            $cubicPrices = $cubicPrices->only('amount', 'notes');
        }

        $expressPrices = ExpressPrice::where('origin_regency_id', $originId)
            ->where('destination_id', $destinationId)
            ->where('service_code', Service::TRAWLPACK_EXPRESS)
            ->first();

        if ($expressPrices !== null) {
            $expressPrices = $expressPrices->only('amount', 'notes');
        }

        $data = [
            "regular" => $resultPrices,
            "kubikasi" => $cubicPrices,
            "express" =>  $expressPrices
        ];

        return (new Response(Response::RC_SUCCESS, $data))->json();
    }

    private function getPrice($serviceCode): JsonResponse
    {
        switch ($serviceCode) {
            case Service::TRAWLPACK_STANDARD:
                $prices = Price::query();
                !Arr::has($this->attributes, 'origin_id') ?: $prices = $this->filterOrigin($prices);
                !Arr::has($this->attributes, 'destination_id') ?: $prices = $this->filterDestination($prices);
                !Arr::has($this->attributes, 'service_code') ?: $prices = $this->filterService($prices);

                $origin_id = (int) $this->attributes['origin_id'];
                $destination_id = (int) $this->attributes['destination_id'];

                $prices = Price::where('origin_regency_id', $origin_id)
                    ->where('destination_id', $destination_id)
                    ->where('service_code', $this->attributes['service_code'])
                    ->first();

                if (is_null($prices)) {
                    $message = ['message' => 'Lokasi tujuan belum tersedia, silahkan hubungi customer kami'];
                    return (new Response(Response::RC_SUCCESS, $message))->json();
                }

                return (new Response(Response::RC_SUCCESS, $prices))->json();
                break;

            case Service::TRAWLPACK_CUBIC:
                $prices = CubicPrice::query();

                !Arr::has($this->attributes, 'origin_id') ?: $prices = $this->filterOrigin($prices);
                !Arr::has($this->attributes, 'destination_id') ?: $prices = $this->filterDestination($prices);
                !Arr::has($this->attributes, 'service_code') ?: $prices = $this->filterService($prices);

                $origin_id = (int) $this->attributes['origin_id'];
                $destination_id = (int) $this->attributes['destination_id'];

                $prices = CubicPrice::where('origin_regency_id', $origin_id)
                    ->where('destination_id', $destination_id)
                    ->where('service_code', $this->attributes['service_code'])
                    ->first();

                if (is_null($prices)) {
                    $message = ['message' => 'Lokasi tujuan belum tersedia, silahkan hubungi customer kami'];
                    return (new Response(Response::RC_SUCCESS, $message))->json();
                }

                return $this->jsonSuccess(CheckPriceResource::make($prices));
                break;
            case Service::TRAWLPACK_EXPRESS:
                $prices = ExpressPrice::query();

                !Arr::has($this->attributes, 'origin_id') ?: $prices = $this->filterOrigin($prices);
                !Arr::has($this->attributes, 'destination_id') ?: $prices = $this->filterDestination($prices);
                !Arr::has($this->attributes, 'service_code') ?: $prices = $this->filterService($prices);

                $origin_id = (int) $this->attributes['origin_id'];
                $destination_id = (int) $this->attributes['destination_id'];

                $prices = ExpressPrice::where('origin_regency_id', $origin_id)
                    ->where('destination_id', $destination_id)
                    ->where('service_code', $this->attributes['service_code'])
                    ->first();

                if (is_null($prices)) {
                    $message = ['message' => 'Lokasi tujuan belum tersedia, silahkan hubungi customer kami'];
                    return (new Response(Response::RC_SUCCESS, $message))->json();
                }

                return $this->jsonSuccess(CheckPriceResource::make($prices));
                break;
        }
    }
}
