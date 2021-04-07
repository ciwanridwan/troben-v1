<?php

namespace App\Casts\Package\Items;

use App\Actions\Pricing\PricingCalculator;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class Handling implements CastsAttributes
{
    const TYPE_BUBBLE_WRAP = "bubble wrap";
    const TYPE_PLASTIC = "plastic";
    const TYPE_CARDBOARD = "cardboard";
    const TYPE_WOOD = "wood";
    const TYPE_SANDBAG_SM = "sandbag sm";
    const TYPE_SANDBAG_MD = "sandbag md";
    const TYPE_SANDBAG_L = "sandbag l";
    const TYPE_PALLETE = "pallete";

    /**
     * Cast the given value.
     *
     * @param  \App\Models\Packages\Item  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function get($model, $key, $value, $attributes)
    {
        return collect(json_decode($value))->map(fn(string $type) => [
            'type' => $type,
            'price' => self::calculator($type, $model->height, $model->length, $model->width, $model->weight),
        ]);
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function set($model, $key, $value, $attributes)
    {
        return json_encode($value);
    }

    public static function getTypes(): array
    {
        return [
            self::TYPE_PLASTIC,
            self::TYPE_BUBBLE_WRAP,
            self::TYPE_CARDBOARD,
            self::TYPE_PALLETE,
            self::TYPE_SANDBAG_L,
            self::TYPE_SANDBAG_MD,
            self::TYPE_SANDBAG_SM,
            self::TYPE_WOOD,
        ];
    }

    public static function calculator($type, $height, $length, $width, $weight)
    {
        switch ($type) {
            case self::TYPE_BUBBLE_WRAP:
                $min_price = 10000;
                $base_price = 350;
                $price = ($height + $length + $width) / 3 * $base_price;
                return $price < $min_price ? $min_price : $price;
            case self::TYPE_PLASTIC:
                $min_price = 3000;
                $base_price = 125;
                $price = ($height + $length + $width) / 3 * $base_price;
                return $price < $min_price ? $min_price : $price;
            case self::TYPE_CARDBOARD:
                $min_price = 10000;
                $base_price = 350;
                $price = ($height + $length + $width) / 3 * $base_price;
                return $price < $min_price ? $min_price : $price;
            case self::TYPE_SANDBAG_SM:
                $base_price = 7500;
                return $base_price;
            case self::TYPE_SANDBAG_MD:
                $base_price = 10000;
                return $base_price;
            case self::TYPE_SANDBAG_L:
                $base_price = 12500;
                return $base_price;
            case self::TYPE_PALLETE:
                $base_price = 2000;
                $price = ($length + $width) / 2 * $base_price;
                return $price;
            case self::TYPE_WOOD:
                $add_dimension = 7; // added 7cm each dimension
                $min_price = 50000;

                $volume = PricingCalculator::getVolume($height, $length, $width);
                $volume_packed = PricingCalculator::getVolume($height + $add_dimension, $length + $add_dimension, $width + $add_dimension);
                $weight = PricingCalculator::ceilByTolerance($weight);

                if ($weight > $volume_packed) {
                    $volume_diff = $volume_packed - $volume;
                    $weight += $volume_diff;
                } else {
                    $weight = $volume_packed;
                }

                $price = $weight * 0.8;

                return $price < $min_price ? $min_price : $price;
            default:
                # code...
                break;
        }
    }
}
