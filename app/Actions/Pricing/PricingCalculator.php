<?php

namespace App\Actions\Pricing;

use App\Models\Price;
use App\Http\Response;
use App\Models\Service;
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
            $this->act_volume = $this->ceilByTolerance(
                $this->getVolume(
                    $this->attributes['height'],
                    $this->attributes['length'],
                    $this->attributes['width'],
                    Arr::get($this->attributes, 'service', Service::TRAWLPACK_STANDARD)
                )
            );
        }
        $this->act_weight = $this->ceilByTolerance($this->attributes['weight']);
    }

    public function calculate(): JsonResponse
    {
        $weight = $this->act_weight > $this->act_volume ? $this->act_weight : $this->act_volume;

        // check if lt min weight
        $weight > Price::MIN_WEIGHT ?: $weight = Price::MIN_WEIGHT;

        $this->tier = $this->getTier($this->price, $weight);

        $dimension_charge = $this->getDimensionCharge(
            $this->attributes['origin_province_id'],
            $this->attributes['origin_regency_id'],
            $this->attributes['destination_id'],
            $this->attributes['height'],
            $this->attributes['length'],
            $this->attributes['width'],
            $this->attributes['weight'],
            Arr::get($this->attributes, 'service', Service::TRAWLPACK_STANDARD)
        );

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

    /**
     * @param int $weight
     * @param int $tier
     *
     * @return float|int
     */
    public static function getDimensionCharge($origin_province_id,   $origin_regency_id,  $destination_id, $height = 0, $length = 0, $width = 0, $weight = 0, $service = Service::TRAWLPACK_STANDARD)
    {
        $price = self::getPrice($origin_province_id, $origin_regency_id, $destination_id);
        $act_weight = self::ceilByTolerance($weight);
        $act_volume = self::ceilByTolerance(
            self::getVolume(
                $height,
                $length,
                $width,
                $service
            )
        );
        $weight = $act_weight > $act_volume ? $act_weight : $act_volume;

        // check if lt min weight
        $weight > Price::MIN_WEIGHT ?: $weight = Price::MIN_WEIGHT;

        $tier = self::getTier($price, $weight);

        return $weight * $tier;
    }

    /**
     * @param int $origin_province_id
     * @param int $origin_regency_id
     * @param int $destination_id
     *
     * @return Price
     * @throws \Throwable
     */
    public static function getPrice($origin_province_id,   $origin_regency_id,  $destination_id): Price
    {
        /** @var Price $price */
        $price = Price::query()->where('origin_province_id', $origin_province_id)->where('origin_regency_id', $origin_regency_id)->where('destination_id', $destination_id)->first();

        throw_if($price === null, Error::make(Response::RC_OUT_OF_RANGE));

        return $price;
    }

    public static function getTier(Price $price, float $weight = 0)
    {
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

    public static function ceilByTolerance(float $weight = 0)
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

        return $weight;
    }

    public static function getVolume($height, $length, $width, $service = Service::TRAWLPACK_STANDARD)
    {
        switch ($service) {
            case Service::TRAWLPACK_EXPRESS:
                $divider = Price::DIVIDER_UDARA;
                break;
            case Service::TRAWLPACK_STANDARD:
            default:
                $divider = Price::DIVIDER_DARAT;
                break;
        }

        // volume formula
        // HEIGHT * LENGTH * WIDTH
        $volume = $height * $length * $width;
        // divide by divider
        $volume /= $divider;

        // volume < 1?1:volume
        $volume = $volume > 1 ? $volume : 1;

        return $volume;
    }
}
