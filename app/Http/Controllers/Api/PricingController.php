<?php

namespace App\Http\Controllers\Api;

use App\Models\Price;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\PriceResource;
use Illuminate\Database\Eloquent\Builder;

class PricingController extends Controller
{
    /**
     * @var array
     */
    protected $attributes = [];

    public function index(Request $request)
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

    public function filterOrigin(Builder $query): Builder
    {
        $query = $query->where('origin_sub_district_id', $this->attributes['origin_id']);

        return $query;
    }
    public function filterDestination(Builder $query): Builder
    {
        $query = $query->where('destination_id', $this->attributes['destination_id']);

        return $query;
    }
    public function filterService(Builder $query): Builder
    {
        $query = $query->where('service_code', $this->attributes['service_code']);

        return $query;
    }
}
