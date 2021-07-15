<?php

namespace App\Actions\Pricing;

use App\Models\Partners\Transporter;
use App\Models\Price;
use App\Http\Response;
use App\Models\Service;
use App\Exceptions\Error;
use Illuminate\Support\Arr;
use Illuminate\Http\JsonResponse;
use App\Casts\Package\Items\Handling;
use App\Http\Resources\PriceResource;
use App\Models\Packages\Item;
use App\Models\Packages\Package;
use App\Models\Packages\Price as PackagesPrice;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PricingCalculator
{
    public const INSURANCE_MIN = 1000;

    public const INSURANCE_MUL = 0.2 / 100;

    public const MIN_TOL = .3;
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

    public static function getPackageTotalAmount(Package $package)
    {
        if (! $package->relationLoaded('items.prices')) {
            $package->load('items.prices');
        }
        if (! $package->relationLoaded('prices')) {
            $package->load('prices');
        }

        // get handling and insurance prices
        $handling_price = 0;
        $insurance_price = 0;
        $package->items()->each(function (Item $item) use (&$handling_price, &$insurance_price) {
            if ($item->handling) {
                $handling_price += (array_sum(array_column($item->handling, 'price')) * $item->qty);
            }
            $insurance_price += ($item->prices()->where('type', PackagesPrice::TYPE_INSURANCE)->get()->sum('amount') * $item->qty);
        });
        $service_price = $package->prices()->where('type', PackagesPrice::TYPE_SERVICE)->get()->sum('amount');
        $discount_price = $package->prices()->where('type', PackagesPrice::TYPE_DISCOUNT)->get()->sum('amount');
        $pickup_price = $package->prices()->where('type', PackagesPrice::TYPE_DELIVERY)->get()->sum('amount');

        $total_amount = $handling_price + $insurance_price + $service_price + $pickup_price - $discount_price;

        return $total_amount;
    }

    /**
     * @param array $inputs
     * @param string $returnType="json"|"array"
     *
     * @return JsonResponse|array
     */
    public static function calculate(array $inputs, string $returnType = 'json')
    {
        $inputs =  Validator::validate($inputs, [
            'origin_province_id' => ['required', 'exists:geo_provinces,id'],
            'origin_regency_id' => ['required', 'exists:geo_regencies,id'],
            'destination_id' => ['required', 'exists:geo_sub_districts,id'],
            'fleet_name' => ['nullable'],
            'items' => ['required'],
            'items.*.height' => ['required', 'numeric'],
            'items.*.length' => ['required', 'numeric'],
            'items.*.width' => ['required', 'numeric'],
            'items.*.weight' => ['required', 'numeric'],
            'items.*.qty' => ['required', 'numeric'],
            'items.*.handling' => ['nullable']
        ]);

        /** @var Price $price */
        $price = self::getPrice($inputs['origin_province_id'], $inputs['origin_regency_id'], $inputs['destination_id']);

        $totalWeightBorne = self::getTotalWeightBorne($inputs['items']);
        $insurancePriceTotal = 0;
        $pickup_price = 0;

        if ($inputs['fleet_name'] == 'bike') {
            $pickup_price = Transporter::PRICE_BIKE;
        } else {
            $pickup_price = Transporter::PRICE_CAR;
        }

        $discount = $pickup_price;

        foreach ($inputs['items'] as $index => $item) {
            $item['handling'] = self::checkHandling($item['handling']);
            $item['weight_borne'] = self::getWeightBorne($item['height'], $item['length'], $item['width'], $item['weight'], 1, $item['handling']);
            $item['weight_borne_total'] = self::getWeightBorne($item['height'], $item['length'], $item['width'], $item['weight'], $item['qty'], $item['handling']);

            if ($item['insurance'] == false ){
                $item['insurance_price'] = 0;
                $item['insurance_price_total'] = 0;
            }else{
                $item['insurance_price'] = self::getInsurancePrice($item['price']);
                $item['insurance_price_total'] = self::getInsurancePrice($item['price'] * $item['qty']);
            }
            $inputs['items'][$index] = $item;
            $insurancePriceTotal += $item['insurance_price_total'];
        }

        $tierPrice = self::getTier($price, $totalWeightBorne);

        $servicePrice = self::getServicePrice($inputs, $price);

        $response = [
            'price' => PriceResource::make($price),
            'items' => $inputs['items'],
            'result' => [
                'insurance_price_total' => $insurancePriceTotal,
                'total_weight_borne' => $totalWeightBorne,
                'pickup_price' => $pickup_price,
                'discount' => $discount,
                'tier' => $tierPrice,
                'service' => $servicePrice
            ]
        ];

        switch ($returnType) {
            case 'array':
                return $response;
            case 'json':
                return (new Response(Response::RC_SUCCESS, $response))->json();
                break;

            default:
                return (new Response(Response::RC_SUCCESS, $response))->json();
                break;
        }
    }

    public static function getServicePrice(array $inputs, ?Price $price = null)
    {
        $inputs =  Validator::validate($inputs, [
            'origin_province_id' => [Rule::requiredIf(! $price), 'exists:geo_provinces,id'],
            'origin_regency_id' => [Rule::requiredIf(! $price), 'exists:geo_regencies,id'],
            'destination_id' => [Rule::requiredIf(! $price), 'exists:geo_sub_districts,id'],
            'items' => ['required'],
            'items.*.height' => ['required', 'numeric'],
            'items.*.length' => ['required', 'numeric'],
            'items.*.width' => ['required', 'numeric'],
            'items.*.weight' => ['required', 'numeric'],
            'items.*.qty' => ['required', 'numeric'],
            'items.*.handling' => ['nullable']
        ]);

        if (! $price) {
            /** @var Price $price */
            $price = self::getPrice($inputs['origin_province_id'], $inputs['origin_regency_id'], $inputs['destination_id']);
        }

        $totalWeightBorne = self::getTotalWeightBorne($inputs['items']);

        $tierPrice = self::getTier($price, $totalWeightBorne);

        $servicePrice = $tierPrice * $totalWeightBorne;
        return $servicePrice;
    }


    public static function getTotalWeightBorne(array $items)
    {
        $items =  Validator::validate($items, [
            '*.height' => ['required', 'numeric'],
            '*.length' => ['required', 'numeric'],
            '*.width' => ['required', 'numeric'],
            '*.weight' => ['required', 'numeric'],
            '*.qty' => ['required', 'numeric'],
            '*.handling' => ['nullable']
        ]);

        $totalWeightBorne = 0;

        foreach ($items as  $item) {
            $item['handling'] = self::checkHandling($item['handling']);
            $totalWeightBorne += self::getWeightBorne($item['height'], $item['length'], $item['width'], $item['weight'], $item['qty'], $item['handling']);
        }
        return $totalWeightBorne;
    }

    public static function getWeightBorne($height = 0, $length = 0, $width = 0, $weight = 0, $qty = 1, $handling = [], $service = Service::TRAWLPACK_STANDARD)
    {
        $handling = self::checkHandling($handling);
        if (in_array(Handling::TYPE_WOOD, $handling)) {
            $weight = Handling::woodWeightBorne($height, $length, $width, $weight);
        } else {
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
        }

        $weight = $weight * $qty;

        // check if lt min weight
        $weight > Price::MIN_WEIGHT ?: $weight = Price::MIN_WEIGHT;
        return $weight;
    }

    public static function getInsurancePrice($price)
    {
        return $price > self::INSURANCE_MIN ? $price * self::INSURANCE_MUL : 0;
    }


    /**
     * @param int $weight
     * @param int $tier
     *
     * @return float|int
     */
    public static function getDimensionCharge($origin_province_id, $origin_regency_id, $destination_id, $height = 0, $length = 0, $width = 0, $weight = 0, $qty = 1, $service = Service::TRAWLPACK_STANDARD, $handling = null)
    {
        $price = self::getPrice($origin_province_id, $origin_regency_id, $destination_id);
        if ($handling === Handling::TYPE_WOOD) {
            $weight = Handling::woodWeightBorne($height, $length, $width, $weight);
        } else {
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
        }

        $weight = $weight * $qty;

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
    public static function getPrice($origin_province_id, $origin_regency_id, $destination_id): Price
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
        $whole = $weight;
        $maj = (int) $whole; //get major
        $min = $whole - $maj; //get after point

        // check with tolerance
        if ($min >= self::MIN_TOL) {
            $min = 1;
        }

        $weight = $maj + $min;

        return $weight;
    }

    public static function getWeight($height = 0, $length = 0, $width = 0, $weight = 0)
    {
        $weight = self::ceilByTolerance($weight);
        $volume = self::ceilByTolerance(self::getVolume($height, $length, $width));
        $weight = $weight > $volume ? $weight : $volume;
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

    private static function checkHandling($handling = [])
    {
        $handling = Arr::wrap($handling);

        if ($handling !== []) {
            if (Arr::has($handling, 'type')) {
                $handling = array_column($handling, 'type');
            }
        }

        return $handling;
    }
}
