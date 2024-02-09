<?php

namespace App\Casts\Package\Items;

use App\Actions\Pricing\PricingCalculator;
use App\Models\Packages\MotorBike;
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
            'price' => ceil(self::calculator($type, $model->height, $model->length, $model->width, $model->weight, $model->id)),
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

    public static function woodWeightBorne($height, $length, $width, $weightActual, $serviceCode)
    {
        /*
        old script
        $add_dimension = self::ADD_WOOD_DIMENSION; // added 7cm each dimension
        $volume = PricingCalculator::getVolume($height, $length, $width, $serviceCode);
        $volume_packed = PricingCalculator::getVolume($height + $add_dimension, $length + $add_dimension, $width + $add_dimension, $serviceCode);

        if ($weight > $volume_packed) {
            $volume_diff = $volume_packed - $volume;
            $weight += $volume_diff;
        } else {
            $weight = $volume_packed;
        }
        */

        // new calculate
        $add_dimension = self::ADD_WOOD_DIMENSION; // added 7cm each dimension
        $volumeBefore = PricingCalculator::getVolume($height, $length, $width, $serviceCode);
        $volumeAfter = PricingCalculator::getVolume($height + $add_dimension, $length + $add_dimension, $width + $add_dimension, $serviceCode);

        $weight = ($volumeAfter - $volumeBefore) + $weightActual;
        return $weight > $volumeAfter ? $weight : $volumeAfter;
    }

    public static function calculator($type, $height, $length, $width, $weight, $itemId = null)
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
                // $add_dimension = self::ADD_WOOD_DIMENSION;
                // $price = ceil(($height + $add_dimension) * ($length + $add_dimension) * ($width + $add_dimension) * 0.6);
                $price = ceil($height * $length * $width * 0.6);

                return $price < $min_price ? $min_price : $price;
            case self::TYPE_BIKES:
                $cc = MotorBike::query()->where('package_item_id', $itemId)->first()->cc;
                $price = self::bikeCalculator($cc);
                return $price;
            default:
                return 0;
        }
    }

    public static function bikeCalculator($cc)
    {
        switch (true) {
            case $cc <= 149:
                $price = 175000;
                return $price;
                break;
            case $cc === 150:
                $price = 250000;
                return $price;
                break;
            case $cc >= 250:
                $price = 450000;
                return $price;
                break;
            default:
                $price = 0;
                return $price;
                break;
        }
    }

    public static function switchDimension($type, $height, $width, $length)
    {
        $dimensions = [
            'height' => $height,
            'width' => $width,
            'length' => $length
        ];

        if (is_array($type)) {
            foreach ($type as $value) {
                if ($value['type'] === self::TYPE_WOOD) {
                    $dimensions['height'] += self::ADD_WOOD_DIMENSION;
                    $dimensions['width'] += self::ADD_WOOD_DIMENSION;
                    $dimensions['length'] += self::ADD_WOOD_DIMENSION;
                }
            }
        }

        return $dimensions;
    }

    public static function woodWeightNew($weight, $height, $length, $width, $serviceCode)
    {
        $addDimension = self::ADD_WOOD_DIMENSION;
        $height += $addDimension;
        $width += $addDimension;
        $length += $addDimension;
        $volume = PricingCalculator::ceilByTolerance(PricingCalculator::getVolume($height, $length, $width, $serviceCode));

        $result = [
            'height' => $height,
            'length' => $length,
            'width' => $width,
            'volume' => $volume,
        ];

        return $result;
    }
}
