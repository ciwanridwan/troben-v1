<?php

namespace App\Actions\Pricing;

use App\Models\Price;
use App\Http\Response;
use App\Exceptions\Error;
use Illuminate\Support\Arr;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\PriceCalculatorResource;

class PricingCalculator
{
    /**
     * @var array
     */
    public array $attributes;

    /**
     * @var Price
     */
    public Price $price;

    /**
     * @var float
     */
    public float $tier;

    /**
     * @var float
     */
    public float $act_weight = 0;

    /**
     * @var float
     */
    public float $act_volume = 0;

    public function __construct($inputs = [])
    {
        $this->attributes = $inputs;
        $this->price = $this->getPrice($this->attributes['origin_province_id'], $this->attributes['origin_regency_id'], $this->attributes['destination_id']);
        if (Arr::has($this->attributes, ['height', 'length', 'width'])) {
            $this->act_volume = $this->getVolume();
        }
        $this->act_weight = $this->attributes['weight'];
    }

    public function calculate(): JsonResponse
    {
        $weight = $this->act_weight > $this->act_volume ? $this->act_weight : $this->act_volume;

        // check if lt min weight
        $weight > Price::MIN_WEIGHT ?: $weight = Price::MIN_WEIGHT;

        $this->tier = $this->getTier($this->price, $weight);

        $dimension_charge = $this->getDimensionCharge($weight, $this->tier);

        $goods_property = Arr::only($this->attributes, ['height', 'width', 'length', 'weight']);

        $response = [
            'price' => $this->price,
            'actual_property' => $goods_property,
            'dimension' => $dimension_charge,
            'weight' => $weight,
            'tier' => $this->tier,
        ];

        return (new Response(Response::RC_SUCCESS, PriceCalculatorResource::make($response)))->json();
    }

    public function getDimensionCharge($weight, $tier = 0)
    {
        $price = $weight * $tier;

        return $price;
    }

    /**
     * @param int $origin_province_id
     * @param int $origin_regency_id
     * @param int $destination_id
     *
     * @return Price
     */
    public function getPrice(int $origin_province_id, int  $origin_regency_id, int $destination_id): Price
    {
        $price = Price::query()->where('origin_province_id', $origin_province_id)->where('origin_regency_id', $origin_regency_id)->where('destination_id', $destination_id)->first();

        throw_if($price === null, Error::make(Response::RC_OUT_OF_RANGE));

        return $price;
    }

    public function getTier(Price $price, float $weight = 0)
    {
        // decimal tolerance .3
        $tol = .3;
        $whole = $weight;
        $maj = (int) $whole; //get major
        $min = $whole - $maj; //get after point

        // check with tolerance
        if ($min >= $tol) {
            $min = 1;
        }

        $weight = $maj + $min;

        if ($weight <= Price::TIER_1) {
            return $price->tier_1;
        } elseif ($weight <= Price::TIER_2) {
            return $price->tier_2;
        } elseif ($weight <= Price::TIER_3) {
            return $price->tier_3;
        } elseif ($weight <= Price::TIER_4) {
            return $price->tier_4;
        } elseif ($weight <= Price::TIER_5) {
            return $price->tier_5;
        } elseif ($weight <= Price::TIER_6) {
            return $price->tier_6;
        } else {
            return $price->tier_7;
        }
    }

    public function getVolume($service = 'darat')
    {
        $divider = 0;
        switch ($divider) {
            case 'darat':
                $divider = Price::DIVIDER_DARAT;
                break;

            case 'udara':
                $divider = Price::DIVIDER_UDARA;
                break;
        }

        // volume formula
        // HEIGHT * LENGTH * WIDTH
        $volume = $this->attributes['height'] * $this->attributes['length'] * $this->attributes['width'];
        // divide by divider
        $volume /= $divider;

        // volume < 1?1:volume
        $volume = $volume > 1 ?: 1;

        return $volume;
    }
}
