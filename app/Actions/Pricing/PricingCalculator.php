<?php

namespace App\Actions\Pricing;

use App\Jobs\Packages\UpdateOrCreatePriceFromExistingPackage;
use App\Models\Packages\Price as PackagePrice;
use App\Models\Partners\Partner;
use App\Models\Partners\Voucher;
use App\Models\Price;
use App\Http\Response;
use App\Models\Promos\Promotion;
use App\Models\Service;
use App\Exceptions\Error;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Arr;
use Illuminate\Http\JsonResponse;
use App\Casts\Package\Items\Handling;
use App\Http\Resources\PriceResource;
use App\Models\Packages\Item;
use App\Models\Packages\Package;
use App\Models\Packages\Price as PackagesPrice;
use App\Models\Partners\Prices\PriceModel as PartnerPrice;
use App\Models\Partners\VoucherAE;
use App\Supports\DistanceMatrix;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PricingCalculator
{
    use DispatchesJobs;

    public const INSURANCE_MIN = 1000;

    public const INSURANCE_MUL = 0.2 / 100;

    public const INSURANCE_MUL_PARTNER = 0.0002;

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

    public static function getPackageTotalAmount(Package $package, bool $is_approved = false)
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

        $pickup_discount_price = $package->prices()->where('type', PackagesPrice::TYPE_DISCOUNT)
            ->where('description', PackagesPrice::TYPE_PICKUP)->get()->sum('amount');

        $service_discount_price = $package->prices()->where('type', PackagesPrice::TYPE_DISCOUNT)
            ->where('description', PackagesPrice::TYPE_SERVICE)->get()->sum('amount');

        $pickup_price = $package->prices()->where('type', PackagesPrice::TYPE_DELIVERY)->get()->sum('amount');

        if ($is_approved == true) {
            $total_amount = $handling_price + $insurance_price + $service_price + $pickup_price - ($pickup_discount_price + $service_discount_price);
        } else {
            $total_amount = $handling_price + $insurance_price + $service_price + $pickup_price - $pickup_discount_price;
        }

        if ($package->claimed_promotion != null) {
            $promo = $package->load('claimed_promotion');
            if ($total_amount < $promo->claimed_promotion->promotion->min_payment) {
                $job = new UpdateOrCreatePriceFromExistingPackage($package, [
                    'type' => PackagePrice::TYPE_SERVICE,
                    'description' => PackagePrice::TYPE_ADDITIONAL,
                    'amount' => $promo->claimed_promotion->promotion->min_payment - $total_amount,
                ]);
                dispatch($job);
                $total_amount = $promo->claimed_promotion->promotion->min_payment;
            }
        }
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
            'partner_code' => ['nullable'],
            'sender_latitude' => ['nullable'],
            'sender_longitude' => ['nullable'],
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
        if (array_key_exists('fleet_name', $inputs) && $inputs['partner_code'] != '' && $inputs['partner_code'] != null) {
            $partner = Partner::where('code', $inputs['partner_code'])->first();
            $origin = $inputs['sender_latitude'].', '.$inputs['sender_longitude'];
            $destination = $partner->latitude.', '.$partner->longitude;
            $distance = DistanceMatrix::calculateDistance($origin, $destination);

            if ($inputs['fleet_name'] == 'bike') {
                if ($distance < 5) {
                    $pickup_price = 8000;
                } else {
                    $substraction = $distance - 4;
                    $pickup_price = 8000 + (2000 * $substraction);
                }
            } else {
                if ($distance < 5) {
                    $pickup_price = 15000;
                } else {
                    $substraction = $distance - 4;
                    $pickup_price = 15000 + (4000 * $substraction);
                }
            }
        }

        $discount = 0;
        $handling_price = 0;

        foreach ($inputs['items'] as $index => $item) {
            if (! Arr::has($item, 'handling')) {
                $item['handling'] = [];
            }
            $handlingResult = [];
            if ($item['handling'] != null) {
                foreach ($item['handling'] as $packing) {
                    $handling = Handling::calculator($packing, $item['height'], $item['length'], $item['width'], $item['weight']);
                    $handling_price += Handling::calculator($packing, $item['height'], $item['length'], $item['width'], $item['weight']);

                    $handlingResult[] = collect([
                        'type' => $packing,
                        'price' => ceil($handling),
                    ]);
                    $item['handling'] = $handlingResult;
                }
            }
            $item['handling'] = self::checkHandling($item['handling']);
            $item['weight_borne'] = self::getWeightBorne($item['height'], $item['length'], $item['width'], $item['weight'], 1, $item['handling']);
            $item['weight_borne_total'] = self::getWeightBorne($item['height'], $item['length'], $item['width'], $item['weight'], $item['qty'], $item['handling']);

            if ($item['insurance'] == false) {
                $item['insurance_price'] = 0;
                $item['insurance_price_total'] = 0;
            } else {
                $item['insurance_price'] = ceil(self::getInsurancePrice($item['price']));
                $item['insurance_price_total'] = ceil(self::getInsurancePrice($item['price'] * $item['qty']));
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
                'handling' => $handling_price,
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

        $items = [];
        foreach ($inputs['items'] as $item) {
            if ($item['handling']) {
                foreach ($item['handling'] as $handling) {
                    $packing[] = [
                        'type' => $handling['type']
                    ];
                }
            }
            $items[] = [
                'weight' => $item['weight'],
                'height' => $item['height'],
                'length' => $item['length'],
                'width' => $item['width'],
                'qty' => $item['qty'],
                'handling' => ! empty($packing) ? array_column($packing, 'type') : null

            ];
        }
        $totalWeightBorne = self::getTotalWeightBorne($items);

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
            if (! Arr::has($item, 'handling')) {
                $item['handling'] = [];
            }
            if (! empty($item['handling'])) {
                $item['handling'] = self::checkHandling($item['handling']);
            }
            $totalWeightBorne += self::getWeightBorne($item['height'], $item['length'], $item['width'], $item['weight'], $item['qty'], $item['handling']);
        }

        return $totalWeightBorne > Price::MIN_WEIGHT ? $totalWeightBorne : Price::MIN_WEIGHT;
    }

    public static function getWeightBorne($height = 0, $length = 0, $width = 0, $weight = 0, $qty = 1, $handling = [], $service = Service::TRAWLPACK_STANDARD)
    {
        $handling = self::checkHandling($handling);
        if (in_array(Handling::TYPE_WOOD, $handling)) {
            $weight = Handling::woodWeightBorne($height, $length, $width, $weight);
        } else {
            $act_weight = $weight;
            $act_volume = self::getVolume(
                $height,
                $length,
                $width,
                $service
            );
            $weight = $act_weight > $act_volume ? $act_weight : $act_volume;
        }

        return (self::ceilByTolerance($weight) * $qty);
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

    /**
     * Get price by tier.
     *
     * @param object $price
     * @param float $weight
     * @return mixed
     */
    public static function getTier(object $price, float $weight = 0.0)
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
        } elseif ($weight <= Price::TIER_7) {
            return $price->tier_7;
        } else {
            return $price->tier_8;
        }
    }

    /**
     * Get type for value by weight.
     *
     * @param float $weight
     * @return int
     */
    public static function getTierType(float $weight): int
    {
        if ($weight <= Price::TIER_1) {
            return PartnerPrice::TYPE_TIER_1;
        } elseif ($weight <= Price::TIER_2) {
            return PartnerPrice::TYPE_TIER_2;
        } elseif ($weight <= Price::TIER_3) {
            return PartnerPrice::TYPE_TIER_3;
        } elseif ($weight <= Price::TIER_4) {
            return PartnerPrice::TYPE_TIER_4;
        } elseif ($weight <= Price::TIER_5) {
            return PartnerPrice::TYPE_TIER_5;
        } elseif ($weight <= Price::TIER_6) {
            return PartnerPrice::TYPE_TIER_6;
        } elseif ($weight <= Price::TIER_7) {
            return PartnerPrice::TYPE_TIER_7;
        } else {
            return PartnerPrice::TYPE_TIER_8;
        }
    }

    public static function ceilByTolerance(float $weight = 0)
    {
        // decimal tolerance .3
        $whole = $weight;
        $maj = (int) $whole; //get major
        $min = $whole - $maj; //get after point

        // check with tolerance
        $min = (int) ($min >= self::MIN_TOL ? 1 : 0);

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
        return $volume > 1 ? $volume : 1;
    }


    public static function getDetailPricingPackage(Package $package)
    {
        $handling_price = $package->prices()->where('type', PackagePrice::TYPE_HANDLING)->get()->sum('amount');
        $service_price = $package->prices()->where('type', PackagePrice::TYPE_SERVICE)->where('description', PackagePrice::TYPE_SERVICE)->get()->sum('amount');
        $pickup_price = $package->prices()->where('type', PackagePrice::TYPE_DELIVERY)->get()->sum('amount');
        $insurance_price = $package->prices()->where('type', PackagePrice::TYPE_INSURANCE)->get()->sum('amount');

        $handling_discount = $package->prices()->where('type', PackagePrice::TYPE_DISCOUNT)->where('description', PackagePrice::TYPE_HANDLING)->get()->sum('amount');
        $insurance_discount = $package->prices()->where('type', PackagePrice::TYPE_DISCOUNT)->where('description', PackagePrice::TYPE_INSURANCE)->get()->sum('amount');
        $pickup_discount = $package->prices()->where('type', PackagePrice::TYPE_DISCOUNT)->where('description', PackagePrice::TYPE_PICKUP)->get()->sum('amount');
        $service_discount = $package->prices()->where('type', PackagePrice::TYPE_DISCOUNT)->where('description', PackagePrice::TYPE_SERVICE)->get()->sum('amount');

        $service_fee = $package->prices()->where('type', PackagePrice::TYPE_SERVICE)->where('description', PackagePrice::TYPE_ADDITIONAL)->get()->sum('amount');

        return [
            'service_price' => $service_price,
            'service_price_fee' => $service_fee,
            'service_price_discount' => $service_discount,
            'insurance_price' => $insurance_price ?? 0,
            'insurance_price_discount' => $insurance_discount,
            'packing_price' => $handling_price ?? 0,
            'packing_price_discount' => $handling_discount,
            'pickup_price' => $pickup_price,
            'pickup_price_discount' => $pickup_discount,
        ];
    }

    public static function getCalculationPromoPackage($promotion_hash, Package $package): array
    {
        $promotion = Promotion::byHashOrFail($promotion_hash);
        $prices = $package->prices()->get();
        $service = $prices->where('type', PackagePrice::TYPE_SERVICE)->first();
        if ($package->total_weight <= $promotion->max_weight) {
            $service_discount = $service->amount;
        } else {
            $service_discount = $package->tier_price * $promotion->max_weight;
        }
        $total_payment = $package->total_amount - $service_discount;
        if ($total_payment <= $promotion->min_payment) {
            $service_fee = $promotion->min_payment - $total_payment;
        }
        return [
            'service_price_fee' => $service_fee ?? 0,
            'service_price_discount' => $service_discount ?? 0,
        ];
    }

    public static function getCalculationVoucherPackage(Voucher $voucher, Package $package): array
    {
        $service_price = $package->prices()->where('type', PackagePrice::TYPE_SERVICE)->where('description', PackagePrice::TYPE_SERVICE)->get()->sum('amount');
        $service_discount_price = $package->prices()->where('type', PackagePrice::TYPE_DISCOUNT)->where('description', PackagePrice::TYPE_SERVICE)->get()->sum('amount');
        $percentage_discount = $service_discount_price / $service_price * 100;
        if ($percentage_discount > $voucher->discount) {
            return [
                'service_price_fee' => 0,
                'service_price_discount' => $service_discount_price,
                'voucher_price_discount' => 0,
            ];
        }

        $service_discount = $service_price * ($voucher->discount / 100);
        $service_fee = $service_price - $service_discount; // gk dipakai
        return [
            'service_price_fee' => 0,
            'service_price_discount' => 0,
            'voucher_price_discount' => $service_discount,
        ];
    }

    public static function getCalculationVoucherPackageAE(VoucherAE $voucher, Package $package): array
    {
        $default =  [
            'service_price_fee' =>  0,
            'voucher_price_discount' => 0,
            'service_price_discount' => 0,
        ];

        $service_price = $package->prices()->where('type', PackagePrice::TYPE_SERVICE)->where('description', PackagePrice::TYPE_SERVICE)->get()->sum('amount');

        if ($voucher->type == VoucherAE::VOUCHER_FREE_PICKUP) {
            $pickup_price = $package->prices()->where('type', PackagePrice::TYPE_DELIVERY)->where('description', PackagePrice::TYPE_PICKUP)->get()->sum('amount');
            $default['pickup_price_discount'] = $pickup_price;
        }

        if ($voucher->type == VoucherAE::VOUCHER_DISCOUNT_SERVICE) {
            if ($voucher->discount > 0) {
                $discount = $service_price * ($voucher->discount / 100);
                $default['voucher_price_discount'] = $discount;
            }
            if ($voucher->nominal > 0) {
                if ($voucher->nominal > $service_price) $voucher->nominal = $service_price;
                $discount = $service_price - $voucher->nominal;
                $default['voucher_price_discount'] = $discount;
            }
        }

        return $default;
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
