<?php

namespace App\Casts\Package\Items;

use App\Actions\Pricing\PricingCalculator;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class Handling implements CastsAttributes
{
    public const TYPE_BUBBLE_WRAP = 'bubble wrap'; // plastik gelembung
    public const TYPE_PLASTIC = 'plastic'; // plastik
    public const TYPE_CARDBOARD = 'cardboard'; // kardus
    public const TYPE_WOOD = 'wood'; // kayu
    public const TYPE_SANDBAG_SM = 'sandbag sm'; // karung kecil
    public const TYPE_SANDBAG_MD = 'sandbag md'; // karung sedang
    public const TYPE_SANDBAG_L = 'sandbag l'; // karung besar
    public const TYPE_PALLETE = 'pallete';

    /**Motobikes const */
    public const TYPE_BIKES = 'bike';

    public const ADD_WOOD_DIMENSION = 7;

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
        return json_decode($value, true);
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  \App\Models\Packages\Item  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function set($model, $key, $value, $attributes)
    {
        $data = collect($value)->map(fn (string $type) => [
            'type' => $type,
            'price' => ceil(self::calculator($type, $model->height, $model->length, $model->width, $model->weight)),
        ]);

        return json_encode($data);
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
            self::TYPE_BIKES
        ];
    }

    public static function woodWeightBorne($height, $length, $width, $weight, $serviceCode)
    {
        $add_dimension = self::ADD_WOOD_DIMENSION; // added 7cm each dimension
        $volume = PricingCalculator::getVolume($height, $length, $width, $serviceCode);
        $volume_packed = PricingCalculator::getVolume($height + $add_dimension, $length + $add_dimension, $width + $add_dimension, $serviceCode);

        if ($weight > $volume_packed) {
            $volume_diff = $volume_packed - $volume;
            $weight += $volume_diff;
        } else {
            $weight = $volume_packed;
        }

        return $weight;
    }

    public static function calculator($type, $height, $length, $width, $weight)
    {
        switch ($type) {
            case self::TYPE_BUBBLE_WRAP:
                $min_price = 10000;
                $base_price = 150;
                $price = ($height + $length + $width) / 3 * $base_price;

                return $price < $min_price ? $min_price : $price;
            case self::TYPE_PLASTIC:
                $min_price = 3000;
                $base_price = 85;
                $price = ($height + $length + $width) / 3 * $base_price;

                return $price < $min_price ? $min_price : $price;
            case self::TYPE_CARDBOARD:
                $min_price = 10000;
                $base_price = 150;
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
                $min_price = 50000;
                $add_dimension = self::ADD_WOOD_DIMENSION;
                $price = ceil(($height + $add_dimension) * ($length + $add_dimension) * ($width + $add_dimension) * 0.6);

                return $price < $min_price ? $min_price : $price;
            default:
                return 0;
        }
    }

    public static function bikeCalculator($cc)
    {
        switch ($cc) {
            case 150:
                $price = 150000;
                return $price;
                break;
            case 250:
                $price = 250000;
                return $price;
                break;
            case 999:
                $price = 500000;
                return $price;
                break;
            default:
                $price = 0;
                return $price;
                break;
        }
    }
}
