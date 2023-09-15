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
use App\Exceptions\InvalidDataException;
use App\Exceptions\OutOfRangePricingException;
use App\Http\Resources\Api\Pricings\ExpressPriceResource;
use App\Http\Resources\Api\Pricings\CubicPriceResource;
use App\Http\Resources\PriceResource;
use App\Models\Packages\BikePrices;
use App\Models\Packages\CubicPrice;
use App\Models\Packages\ExpressPrice;
use App\Models\Packages\Item;
use App\Models\Packages\Package;
use App\Models\Packages\Price as PackagesPrice;
use App\Models\Partners\Prices\PriceModel as PartnerPrice;
use App\Models\Partners\VoucherAE;
use App\Models\PartnerSatellite;
use App\Supports\DistanceMatrix;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PricingCalculator
{
    use DispatchesJobs;

    public const INSURANCE_MIN = 1000;

    public const INSURANCE_MUL = 0.2 / 100;

    public const INSURANCE_MUL_PARTNER = 0.0002;

    public const INSURANCE_PARTNER = 0.0006;


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
        if (!$package->relationLoaded('items.prices')) {
            $package->load('items.prices');
        }
        if (!$package->relationLoaded('prices')) {
            $package->load('prices');
        }
        if (!$package->relationLoaded('motoBikes')) {
            $package->load('motoBikes');
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

        if (!is_null($package->motoBikes)) {
            $handling_price = 0;
        }

        $service_price = $package->prices()->where('type', PackagesPrice::TYPE_SERVICE)->get()->sum('amount');

        $pickup_discount_price = $package->prices()->where('type', PackagesPrice::TYPE_DISCOUNT)
            ->where('description', PackagesPrice::TYPE_PICKUP)->get()->sum('amount');

        $service_discount_price = $package->prices()->where('type', PackagesPrice::TYPE_DISCOUNT)
            ->where('description', PackagesPrice::TYPE_SERVICE)->get()->sum('amount');

        $pickup_price = $package->prices()->where('type', PackagesPrice::TYPE_DELIVERY)->get()->sum('amount');

        $handlingBikePrices = $package->prices()->where('type', PackagePrice::TYPE_HANDLING)->where('description', Handling::TYPE_BIKES)->get()->sum('amount');

        $platformPrice = $package->prices()->where('type', PackagesPrice::TYPE_PLATFORM)->get()->sum('amount') ?? 0;

        if ($is_approved == true) {
            $total_amount = $handling_price + $insurance_price + $service_price + $pickup_price + $handlingBikePrices  + $platformPrice - ($pickup_discount_price + $service_discount_price);
        } else {
            $total_amount = $handling_price + $insurance_price + $service_price + $pickup_price + $handlingBikePrices + $platformPrice - $pickup_discount_price;
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
            'origin_province_id' => ['required', 'numeric', 'exists:geo_provinces,id'],
            'origin_regency_id' => ['required', 'numeric', 'exists:geo_regencies,id'],
            'destination_id' => ['required', 'numeric', 'exists:geo_sub_districts,id'],
            'service_code' => ['required', 'exists:services,code'],
            'partner_code' => ['nullable'],
            'partner_satellite' => ['nullable'],
            'sender_latitude' => ['nullable'],
            'sender_longitude' => ['nullable'],
            'fleet_name' => ['nullable'],
            'items' => ['nullable'],
            'items.*.height' => ['required', 'numeric'],
            'items.*.length' => ['required', 'numeric'],
            'items.*.width' => ['required', 'numeric'],
            'items.*.weight' => ['required', 'numeric'],
            'items.*.qty' => ['required', 'numeric'],
            'items.*.handling' => ['nullable'],
            'is_multi' => ['nullable', 'boolean']
        ]);

        $serviceCode = $inputs['service_code'];

        switch ($serviceCode) {
            case Service::TRAWLPACK_CUBIC:
                $cubicPrice = self::getCubicPrice($inputs['origin_regency_id'], $inputs['destination_id']);
                break;
            case Service::TRAWLPACK_EXPRESS:
                $expressPrice = self::getExpressPrice($inputs['origin_province_id'], $inputs['origin_regency_id'], $inputs['destination_id']);
                break;
        }

        /** @var Price $price */
        $price = self::getPrice($inputs['origin_province_id'], $inputs['origin_regency_id'], $inputs['destination_id']);

        $totalWeightBorne = self::getTotalWeightBorne($inputs['items'] ?? [], $serviceCode);
        $insurancePriceTotal = 0;
        $pickup_price = 0;

        if (array_key_exists('fleet_name', $inputs) && isset($inputs['partner_code']) && $inputs['partner_code'] != '' && $inputs['partner_code'] != null) {
            $partner = Partner::where('code', $inputs['partner_code'])->first();
            $origin = $inputs['sender_latitude'] . ', ' . $inputs['sender_longitude'];
            $destination = $partner->latitude . ', ' . $partner->longitude;

            if (isset($inputs['partner_satellite']) && $inputs['partner_satellite']) {
                $partnerSatellite = PartnerSatellite::where('id_partner', $partner->getKey())->where('id', $inputs['partner_satellite'])->first();
                if (!is_null($partnerSatellite)) {
                    // override destination partner
                    $destination = $partnerSatellite->latitude . ', ' . $partnerSatellite->longitude;
                }
            }

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

        if (isset($inputs['is_multi']) && $inputs['is_multi']) {
            $pickup_price = 0;
        }

        $discount = 0;
        $handling_price = 0;

        if (!isset($inputs['items'])) {
            throw_if($price === null, InvalidDataException::make(Response::RC_INVALID_DATA, ["message" => "Items doesn't input, please input items"]));
        }

        foreach (($inputs['items'] ?? []) as $index => $item) {
            if (!Arr::has($item, 'handling')) {
                $item['handling'] = [];
            }
            $handlingResult = [];
            if ($item['handling'] != null) {
                foreach ($item['handling'] as $packing) {
                    $packingType = $packing;
                    if (is_array($packingType) && isset($packingType['type'])) {
                        $packingType = $packingType['type'];
                    }

                    $handling = Handling::calculator($packingType, $item['height'], $item['length'], $item['width'], $item['weight']);
                    $handling_price += Handling::calculator($packingType, $item['height'], $item['length'], $item['width'], $item['weight']);
                    $handlingResult[] = collect([
                        'type' => $packing,
                        'price' => ceil($handling),
                    ])->toArray();
                    $item['handling'] = $handlingResult;
                }
            }

            $item['handling'] = self::checkHandling($item['handling']);
            $item['weight_borne'] = self::getWeightBorne($item['height'], $item['length'], $item['width'], $item['weight'], 1, $item['handling']);
            $item['weight_borne_total'] = self::getWeightBorne($item['height'], $item['length'], $item['width'], $item['weight'], $item['qty'], $item['handling']);
            $hasNotInsurance = isset($item['insurance']) && $item['insurance'] == false;
            if ($hasNotInsurance) {
                $item['insurance_price'] = 0;
                $item['insurance_price_total'] = 0;
            } else {
                $item['insurance_price'] = ceil(self::getInsurancePrice($item['price']));
                $item['insurance_price_total'] = ceil(self::getInsurancePrice($item['price'] * $item['qty']));
            }
            $inputs['items'][$index] = $item;
            $insurancePriceTotal += $item['insurance_price_total'];
        }

        switch ($serviceCode) {
            case Service::TRAWLPACK_STANDARD:
                $tierPrice = self::getTier($price, $totalWeightBorne);
                $servicePrice = self::getServicePrice($inputs, $price);
                $result['price'] = PriceResource::make($price);
                $result['tier'] = $tierPrice;
                $result['total_weight_borne'] = $totalWeightBorne;
                $additionalCost = self::getAdditionalPrices($inputs['items'], $serviceCode);
                break;

            case Service::TRAWLPACK_CUBIC:
                $servicePrice = self::getServiceCubicPrice($inputs, $cubicPrice);
                $result['price'] = CubicPriceResource::make($cubicPrice);
                $result['tier'] = $cubicPrice->amount;
                $result['total_weight_borne'] = 0;
                $additionalCost = 0;
                break;

            case Service::TRAWLPACK_EXPRESS:
                $servicePrice = self::getServiceExpressPrice($inputs, $expressPrice);
                $result['price'] = ExpressPriceResource::make($expressPrice);
                $result['tier'] = $expressPrice->amount;
                $result['total_weight_borne'] = $totalWeightBorne;
                $additionalCost = self::getAdditionalPrices($inputs['items'], $serviceCode);
                break;
        }

        $totalAmount = $servicePrice + $pickup_price + $handling_price + $insurancePriceTotal + $additionalCost - $discount;

        $response = [
            'price' => $result['price'],
            'items' => $inputs['items'],
            'result' => [
                'insurance_price_total' => $insurancePriceTotal,
                'total_weight_borne' => $totalWeightBorne,
                'handling' => $handling_price,
                'pickup_price' => $pickup_price,
                'discount' => $discount,
                'tier' => $result['tier'],
                'additional_price' => $additionalCost,
                'service' => $servicePrice,
                'platform_fee' => PackagePrice::FEE_PLATFORM,
                'total_amount' => $totalAmount
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

    /**
     * @param array $inputs
     * @param string $returnType="json"|"array"
     *
     * @return JsonResponse|array
     */
    public static function corporateCalculate(array $inputs, string $returnType = 'json')
    {
        $inputs =  Validator::validate($inputs, [
            'origin_province_id' => ['required', 'numeric', 'exists:geo_provinces,id'],
            'origin_regency_id' => ['required', 'numeric', 'exists:geo_regencies,id'],
            'destination_id' => ['required', 'numeric', 'exists:geo_sub_districts,id'],
            'service_code' => ['required', 'exists:services,code'],
            'partner_code' => ['nullable'],
            'partner_satellite' => ['nullable'],
            'sender_latitude' => ['nullable'],
            'sender_longitude' => ['nullable'],
            'fleet_name' => ['nullable'],
            'items' => ['nullable'],
            'items.*.height' => ['required', 'numeric'],
            'items.*.length' => ['required', 'numeric'],
            'items.*.width' => ['required', 'numeric'],
            'items.*.weight' => ['required', 'numeric'],
            'items.*.qty' => ['required', 'numeric'],
            'items.*.handling' => ['nullable'],
            'is_multi' => ['nullable', 'boolean']
        ]);

        $serviceCode = $inputs['service_code'];

        switch ($serviceCode) {
            case Service::TRAWLPACK_CUBIC:
                $cubicPrice = self::getCubicPrice($inputs['origin_regency_id'], $inputs['destination_id']);
                break;
            case Service::TRAWLPACK_EXPRESS:
                $expressPrice = self::getExpressPrice($inputs['origin_province_id'], $inputs['origin_regency_id'], $inputs['destination_id']);
                break;
        }

        /** @var Price $price */
        $price = self::getPrice($inputs['origin_province_id'], $inputs['origin_regency_id'], $inputs['destination_id']);
        $totalWeightBorne = self::getTotalWeightBorne($inputs['items'] ?? [], $serviceCode);
        $insurancePriceTotal = 0;
        $pickup_price = 0;

        if (array_key_exists('fleet_name', $inputs) && isset($inputs['partner_code']) && $inputs['partner_code'] != '' && $inputs['partner_code'] != null) {
            $partner = Partner::where('code', $inputs['partner_code'])->first();
            $origin = $inputs['sender_latitude'] . ', ' . $inputs['sender_longitude'];
            $destination = $partner->latitude . ', ' . $partner->longitude;

            if (isset($inputs['partner_satellite']) && $inputs['partner_satellite']) {
                $partnerSatellite = PartnerSatellite::where('id_partner', $partner->getKey())->where('id', $inputs['partner_satellite'])->first();
                if (!is_null($partnerSatellite)) {
                    // override destination partner
                    $destination = $partnerSatellite->latitude . ', ' . $partnerSatellite->longitude;
                }
            }

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

        if (isset($inputs['is_multi']) && $inputs['is_multi']) {
            $pickup_price = 0;
        }

        $discount = 0;
        $handling_price = 0;

        if (!isset($inputs['items'])) {
            throw_if($price === null, InvalidDataException::make(Response::RC_INVALID_DATA, ["message" => "Items doesn't input, please input items"]));
        }

        foreach (($inputs['items'] ?? []) as $index => $item) {
            if (!Arr::has($item, 'handling')) {
                $item['handling'] = [];
            }
            $handlingResult = [];
            if ($item['handling'] != null) {
                foreach ($item['handling'] as $packing) {
                    $packingType = $packing;
                    if (is_array($packingType) && isset($packingType['type'])) {
                        $packingType = $packingType['type'];
                    }

                    $handling = Handling::calculator($packingType, $item['height'], $item['length'], $item['width'], $item['weight']);
                    $handling_price += Handling::calculator($packingType, $item['height'], $item['length'], $item['width'], $item['weight']);
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

            $hasNotInsurance = isset($item['insurance']) && $item['insurance'] == false;
            if ($hasNotInsurance) {
                $item['insurance_price'] = 0;
                $item['insurance_price_total'] = 0;
            } else {
                $item['insurance_price'] = ceil(self::getInsurancePrice($item['price']));
                $item['insurance_price_total'] = ceil(self::getInsurancePrice($item['price'] * $item['qty']));
            }
            $inputs['items'][$index] = $item;
            $insurancePriceTotal += $item['insurance_price_total'];
        }

        switch ($serviceCode) {
            case Service::TRAWLPACK_STANDARD:
                $tierPrice = self::getTier($price, $totalWeightBorne);
                $servicePrice = self::getServicePrice($inputs, $price);
                $result['price'] = PriceResource::make($price);
                $result['tier'] = $tierPrice;
                $result['total_weight_borne'] = $totalWeightBorne;
                $additionalCost = self::getAdditionalPrices($inputs['items'], $serviceCode);
                break;

            case Service::TRAWLPACK_CUBIC:
                $servicePrice = self::getServiceCubicPrice($inputs, $cubicPrice);
                $result['price'] = CubicPriceResource::make($cubicPrice);
                $result['tier'] = $cubicPrice->amount;
                $result['total_weight_borne'] = 0;
                $additionalCost = 0;
                break;

            case Service::TRAWLPACK_EXPRESS:
                $servicePrice = self::getServiceExpressPrice($inputs, $expressPrice);
                $result['price'] = ExpressPriceResource::make($expressPrice);
                $result['tier'] = $expressPrice->amount;
                $result['total_weight_borne'] = $totalWeightBorne;
                $additionalCost = self::getAdditionalPrices($inputs['items'], $serviceCode);
                break;
        }

        $totalAmount = $servicePrice + $pickup_price + $handling_price + $insurancePriceTotal + $additionalCost + PackagePrice::FEE_PLATFORM - $discount;

        $response = [
            'price' => $result['price'],
            'items' => $inputs['items'],
            'result' => [
                'insurance_price_total' => $insurancePriceTotal,
                'total_weight_borne' => $totalWeightBorne,
                'handling' => $handling_price,
                'pickup_price' => $pickup_price,
                'discount' => $discount,
                'tier' => $result['tier'],
                'additional_price' => $additionalCost,
                'service' => $servicePrice,
                'platform_fee' => PackagePrice::FEE_PLATFORM,
                'total_amount' => $totalAmount
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
            'origin_province_id' => [Rule::requiredIf(!$price), 'exists:geo_provinces,id'],
            'origin_regency_id' => [Rule::requiredIf(!$price), 'exists:geo_regencies,id'],
            'destination_id' => [Rule::requiredIf(!$price), 'exists:geo_sub_districts,id'],
            'items' => ['required'],
            'items.*.height' => ['required', 'numeric'],
            'items.*.length' => ['required', 'numeric'],
            'items.*.width' => ['required', 'numeric'],
            'items.*.weight' => ['required', 'numeric'],
            'items.*.qty' => ['required', 'numeric'],
            'items.*.handling' => ['nullable']
        ]);

        if (!$price) {
            /** @var Price $price */
            $price = self::getPrice($inputs['origin_province_id'], $inputs['origin_regency_id'], $inputs['destination_id']);
        }

        $items = [];
        $itemsTemp = $inputs['items'] ?? [];
        foreach ($itemsTemp as $key => $item) {
            $packing = [];
            if ($item['handling']) {
                foreach ($item['handling'] as $handling) {
                    if ($handling instanceof Collection) {
                        $checkHandling = $handling->toArray();
                    } else {
                        $checkHandling = $handling;
                    }

                    if (!is_array($checkHandling)) {
                        $checkHandling = array($checkHandling);
                    }

                    if (array_key_exists('type', $item['handling']) || array_key_exists('type', $checkHandling)) {
                        $packing[] = [
                            'type' => $handling['type']
                        ];
                    } else {
                        if (is_array($handling) && array_key_exists("type", $handling)) {
                            $packing[] = [
                                'type' => $handling['type']
                            ];
                        } else {
                            $packing[] = [
                                'type' => $handling
                            ];
                        }
                    }
                }
            }

            $items[] = [
                'weight' => $item['weight'],
                'height' => $item['height'],
                'length' => $item['length'],
                'width' => $item['width'],
                'qty' => $item['qty'],
                'handling' => !empty($packing) ? array_column($packing, 'type') : null
            ];
        }
        # still get bug, not use
        // $totalWeightBorne = self::getTotalWeightBorne($items, Service::TRAWLPACK_STANDARD);

        # use this total weight, get by sum
        if (count($itemsTemp) >= 1 && isset($itemsTemp[0]['weight_borne_total'])) {
            $totalWeightBorne = array_sum(array_column($itemsTemp, 'weight_borne_total'));
        } else {
            $totalWeightBorne = self::getTotalWeightBorne($itemsTemp, Service::TRAWLPACK_STANDARD);
        }

        $tierPrice = self::getTier($price, $totalWeightBorne);

        $servicePrice = $tierPrice * $totalWeightBorne;

        $log = [
            'tier_price' => $tierPrice,
            'total_weight_borne' => $totalWeightBorne,
            'service_price' => $servicePrice
        ];

        Log::info('this is tier price => ', $log);

        return $servicePrice;
    }


    public static function getTotalWeightBorne(array $items, string $serviceCode)
    {
        $items =  Validator::validate($items, [
            '*.height' => ['required', 'numeric'],
            '*.length' => ['required', 'numeric'],
            '*.width' => ['required', 'numeric'],
            '*.weight' => ['required', 'numeric'],
            '*.qty' => ['required', 'numeric'],
            '*.handling' => ['nullable']
        ]);

        $totalWeightBorne = [];

        foreach ($items as  $item) {
            if (!Arr::has($item, 'handling')) {
                $item['handling'] = [];
            }
            if (!empty($item['handling'])) {
                $item['handling'] = self::checkHandling($item['handling']);
            }
            $totalWeight = self::getWeightBorne($item['height'], $item['length'], $item['width'], $item['weight'], $item['qty'], $item['handling'], $serviceCode);
            array_push($totalWeightBorne, $totalWeight);
        }
        $total = array_sum($totalWeightBorne);
        return $total > Price::MIN_WEIGHT ? $total : Price::MIN_WEIGHT;
    }

    public static function getWeightBorne($height = 0, $length = 0, $width = 0, $weight = 0, $qty = 1, $handling = [], $serviceCode = null)
    {
        # not use
        // $handling = self::checkHandling($handling);

        if (in_array(Handling::TYPE_WOOD, $handling) || in_array(Handling::TYPE_WOOD, array_column($handling, 'type'),)) {
            $weight = Handling::woodWeightBorne($height, $length, $width, $weight, $serviceCode);
        } else {
            $act_weight = $weight;
            $act_volume = self::getVolume(
                $height,
                $length,
                $width,
                $serviceCode
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
    public static function getDimensionCharge($origin_province_id, $origin_regency_id, $destination_id, $height = 0, $length = 0, $width = 0, $weight = 0, $qty = 1, $serviceCode, $handling = null)
    {
        $price = self::getPrice($origin_province_id, $origin_regency_id, $destination_id);
        if ($handling === Handling::TYPE_WOOD) {
            $weight = Handling::woodWeightBorne($height, $length, $width, $weight, $serviceCode);
        } else {
            $act_weight = self::ceilByTolerance($weight);
            $act_volume = self::ceilByTolerance(
                self::getVolume(
                    $height,
                    $length,
                    $width,
                    $serviceCode
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

        throw_if($price === null, OutOfRangePricingException::make(Response::RC_OUT_OF_RANGE));

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
        $p = 0;
        if ($weight <= Price::TIER_1) {
            $p = $price->tier_1;
        } elseif ($weight <= Price::TIER_2) {
            $p = $price->tier_2;
        } elseif ($weight <= Price::TIER_3) {
            $p = $price->tier_3;
        } elseif ($weight <= Price::TIER_4) {
            $p = $price->tier_4;
        } elseif ($weight <= Price::TIER_5) {
            $p = $price->tier_5;
        } elseif ($weight <= Price::TIER_6) {
            $p = $price->tier_6;
        } elseif ($weight <= Price::TIER_7) {
            $p = $price->tier_7;
        } else {
            $p = $price->tier_8;
        }

        throw_if($p <= 0, OutOfRangePricingException::make(Response::RC_OUT_OF_RANGE));

        return $p;
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

    public static function getWeight($height = 0, $length = 0, $width = 0, $weight = 0, $serviceCode)
    {
        $weight = self::ceilByTolerance($weight);
        $volume = self::ceilByTolerance(self::getVolume($height, $length, $width, $serviceCode));
        $weight = $weight > $volume ? $weight : $volume;
        return $weight;
    }

    public static function getVolume($height, $length, $width, $serviceCode)
    {
        switch ($serviceCode) {
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
        $platformPrice = $package->prices()->where('type', PackagePrice::TYPE_PLATFORM)->get()->sum('amount') ?? 0;
        // $service_fee = $package->prices()->where('type', PackagePrice::TYPE_SERVICE)->where('description', PackagePrice::TYPE_ADDITIONAL)->get()->sum('amount');

        return [
            'service_price' => $service_price,
            // 'service_price_fee' => $service_fee,
            'service_price_discount' => $service_discount,
            'insurance_price' => $insurance_price ?? 0,
            'insurance_price_discount' => $insurance_discount,
            'packing_price' => $handling_price ?? 0,
            'packing_price_discount' => $handling_discount,
            'pickup_price' => $pickup_price,
            'pickup_price_discount' => $pickup_discount,
            'platform_price' => $platformPrice
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
                if ($voucher->nominal > $service_price) {
                    $voucher->nominal = $service_price;
                }
                $discount = $service_price - $voucher->nominal;
                $default['voucher_price_discount'] = $discount;
            }
        }

        return $default;
    }

    /** Motobikes price */
    public static function getBikePrice($originRegencyId, $destinationId)
    {
        $messages = ['message' => 'Lokasi yang anda pilih belum terjangkau'];

        $price = BikePrices::where('origin_regency_id', $originRegencyId)->where('destination_id', $destinationId)->first();

        throw_if($price === null, OutOfRangePricingException::make(Response::RC_OUT_OF_RANGE, $messages));

        return $price;
    }

    /** Cubic Price */
    public static function getCubicPrice($originRegencyId, $destinationId)
    {
        $price = CubicPrice::where('origin_regency_id', $originRegencyId)->where('destination_id', $destinationId)->first();
        // $message = ['message' => 'Lokasi tujuan belum tersedia, silahkan hubungi customer kami'];

        // throw_if($price === null, Error::make(Response::RC_SUCCESS, $message));
        return $price;
    }

    /**Calculating cubic price */
    public static function getServiceCubicPrice(array $inputs, ?CubicPrice $price = null)
    {
        $inputs =  Validator::validate($inputs, [
            // 'origin_province_id' => [Rule::requiredIf(!$price), 'exists:geo_provinces,id'],
            'origin_regency_id' => [Rule::requiredIf(!$price), 'exists:geo_regencies,id'],
            'destination_id' => [Rule::requiredIf(!$price), 'exists:geo_sub_districts,id'],
            'items' => ['required'],
            'items.*.height' => ['required', 'numeric'],
            'items.*.length' => ['required', 'numeric'],
            'items.*.width' => ['required', 'numeric'],
            'items.*.qty' => ['required', 'numeric'],
            'items.*.handling' => ['nullable']
        ]);

        if (!$price) {
            /** @var Price $price */
            $price = self::getCubicPrice($inputs['origin_regency_id'], $inputs['destination_id']);
        }

        /**Todo calculate */
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
                'weight' => 0,
                'height' => $item['height'],
                'length' => $item['length'],
                'width' => $item['width'],
                'qty' => $item['qty'],
                'handling' => !empty($packing) ? array_column($packing, 'type') : null
            ];
        }

        foreach ($items as $item) {
            $calculateCubic = $item['height'] * $item['width'] * $item['length'] / 1000000;
            $cubic[] = $calculateCubic;
            $cubicResult = array_sum($cubic);
        }

        if ($cubicResult <= 3) {
            $cubicResult = 3;
        }

        $servicePrice = $cubicResult * $price->amount;

        return $servicePrice;
    }

    /** Get Express Price */
    public static function getExpressPrice($originProvinceId, $originRegencyId, $destinationId)
    {
        $price = ExpressPrice::where('origin_province_id', $originProvinceId)->where('origin_regency_id', $originRegencyId)->where('destination_id', $destinationId)->first();
        $message = ['message' => 'Lokasi tujuan belum tersedia, silahkan hubungi customer kami'];

        throw_if($price === null, OutOfRangePricingException::make(Response::RC_OUT_OF_RANGE, $message));

        return $price;
    }

    public static function getServiceExpressPrice(array $inputs, ?ExpressPrice $price = null)
    {
        $inputs =  Validator::validate($inputs, [
            'origin_province_id' => [Rule::requiredIf(!$price), 'exists:geo_provinces,id'],
            'origin_regency_id' => [Rule::requiredIf(!$price), 'exists:geo_regencies,id'],
            'destination_id' => [Rule::requiredIf(!$price), 'exists:geo_sub_districts,id'],
            'items' => ['required'],
            'items.*.weight' => ['required', 'numeric'],
            'items.*.height' => ['required', 'numeric'],
            'items.*.length' => ['required', 'numeric'],
            'items.*.width' => ['required', 'numeric'],
            'items.*.qty' => ['required', 'numeric'],
            'items.*.handling' => ['nullable']
        ]);

        if (!$price) {
            /** @var Price $price */
            $price = self::getExpressPrice($inputs['origin_province_id'], $inputs['origin_regency_id'], $inputs['destination_id']);
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
                'handling' => !empty($packing) ? array_column($packing, 'type') : null
            ];
        }
        $totalWeightBorne = self::getTotalWeightBorne($items, Service::TRAWLPACK_EXPRESS);

        $servicePrice = $price->amount * $totalWeightBorne;

        return $servicePrice;
    }

    /**
     * To add additional price to package_prices tables.
     * @return int $price
     * @param array $items
     * @param string $serviceCode
     */
    public static function getAdditionalPrices($items, $serviceCode)
    {
        $additionalPrice = [];

        foreach ($items as $item) {
            # not use, use $item['weight']
            // $totalWeight = self::getWeightBorne($item['height'], $item['length'], $item['width'], $item['weight'], $item['qty'], $item['handling'], $serviceCode);
            $item['additional_price'] = 0;

            if ($item['weight'] < 100) {
                $item['additional_price'] = 0;
            } elseif ($item['weight'] < 300) {
                $item['additional_price'] = 100000;
            } elseif ($item['weight'] < 2000) {
                $item['additional_price'] = 250000;
            } elseif ($item['weight'] < 5000) {
                $item['additional_price'] = 1500000;
            } else {
                $item['additional_price'] = 0;
            }

            array_push($additionalPrice, $item['additional_price']);
        }

        $price = array_sum($additionalPrice);
        return $price;
    }

    public static function getDetailMultiPricing($package)
    {
        $pickupPrice = $package->prices()->where('type', PackagePrice::TYPE_DELIVERY)->where('description', PackagePrice::TYPE_PICKUP)->first()->amount ?? 0;
        $childPackage = Package::whereIn('id', $package->multiDestination->pluck('child_id'))->get();

        $prices = [];
        foreach ($childPackage as $r) {
            $servicePrice = null;

            switch ($r->service_code) {
                case Service::TRAWLPACK_STANDARD:
                    $servicePrice = $r->prices()->where('type', PackagePrice::TYPE_SERVICE)->where('description', PackagePrice::TYPE_SERVICE)->first();
                    if (is_null($servicePrice)) {
                        $servicePrice = $r->prices()->where('type', PackagePrice::TYPE_SERVICE)->where('description', PackagePrice::DESCRIPTION_TYPE_CUBIC)->first();
                    }
                    break;
                case Service::TRAWLPACK_EXPRESS:
                    $servicePrice = $r->prices()->where('type', PackagePrice::TYPE_SERVICE)->where('description', PackagePrice::DESCRIPTION_TYPE_EXPRESS)->first();
                    break;
                default:
                    break;
            }

            $handlingPrice = $r->prices()->where('type', PackagePrice::TYPE_HANDLING)->get()->sum('amount') ?? 0;

            $insurancePrice = $r->prices()->where('type', PackagePrice::TYPE_INSURANCE)->get()->sum('amount') ?? 0;

            $additionalPrice = $r->prices()->where('type', PackagePrice::TYPE_SERVICE)->where('description', PackagePrice::TYPE_ADDITIONAL)->first()->amount ?? 0;

            $discount = $r->prices()->where('type', PackagePrice::TYPE_DISCOUNT)->first()->amount ?? 0;

            $price = [
                'service_price' => $servicePrice->amount,
                'handling_price' => $handlingPrice,
                'insurance_price' => $insurancePrice,
                'additional_price' => $additionalPrice,
                'discount' => $discount
            ];

            array_push($prices, $price);
        }

        $totalHandling = array_sum(array_column($prices, 'handling_price')) + $package->prices()->where('type', PackagePrice::TYPE_HANDLING)->get()->sum('amount') ?? 0;

        $totalInsurance =  array_sum(array_column($prices, 'insurance_price')) + $package->prices()->where('type', PackagePrice::TYPE_INSURANCE)->get()->sum('amount') ?? 0;

        $serviceFee = $package->prices()->where('type', PackagePrice::TYPE_SERVICE)->where('description', PackagePrice::TYPE_SERVICE)->first() ?? $package->prices()->where('type', PackagePrice::TYPE_SERVICE)->where('description', PackagePrice::DESCRIPTION_TYPE_EXPRESS)->first();
        if (is_null($serviceFee)) {
            $serviceFee = $package->prices()->where('type', PackagePrice::TYPE_SERVICE)->where('description', PackagePrice::DESCRIPTION_TYPE_CUBIC)->first();
        }

        $totalServiceFee = array_sum(array_column($prices, 'service_price')) + $serviceFee->amount ?? 0;

        $totalAdditional = array_sum(array_column($prices, 'additional_price')) + $package->prices()->where('type', PackagePrice::TYPE_SERVICE)->where('description', PackagePrice::TYPE_ADDITIONAL)->first()->amount ?? 0;

        $discount = $package->prices()->where('type', PackagePrice::TYPE_DISCOUNT)->first()->amount ?? array_sum(array_column($prices, 'discount'));

        $platformFee = $r->prices()->where('type', PackagePrice::TYPE_PLATFORM)->get()->sum('amount') ?? 0;

        $totalAmount = Package::whereIn('id', $package->multiDestination->pluck('child_id'))->get()->sum('total_amount') + $package->total_amount + $platformFee - $discount;

        $data = [
            'service_code' => $package->service_code,
            'prices' => $prices,
            'pickup_price' => $pickupPrice,
            'platform_fee' => $platformFee,
            'total_handling_prices' => $totalHandling,
            'total_insurance_prices' => $totalInsurance,
            'total_service_price' => $totalServiceFee,
            'total_additional_price' => $totalAdditional,
            'discount' => $discount,
            'total_amount' => $totalAmount
        ];

        return $data;
    }

    public static function getDetailMultiItems($package)
    {
        $childPackage = Package::whereIn('id', $package->multiDestination->pluck('child_id'))->get();

        $items = [];

        foreach ($childPackage as $r) {
            $item = $r->items()->get();
            $attachments = $r->attachments()->get();
            $notes = Price::query()
                ->where('origin_regency_id', $r->origin_regency_id)
                ->where('destination_id', $r->destination_sub_district_id)
                ->first()->notes ?? null;

            $codePackage = $r->code->content;
            // package child
            $result = [
                'sender_name' => $r->sender_name,
                'sender_address' => $r->sender_address,
                'sender_way_point' => $r->sender_way_point,
                'receiver_name' => $r->receiver_name,
                'receiver_address' => $r->receiver_address,
                'reveiver_way_point' => $r->receiver_way_point,
                'hash' => $r->hash,
                'code' => $codePackage,
                'attachments' => $attachments,
                'items' => $item,
                'notes' => $notes
            ];
            array_push($items, $result);
        }
        // package parent
        $parentData = [
            'parent' => [
                'sender_name' => $package->sender_name,
                'sender_address' => $package->sender_address,
                'sender_way_point' => $package->sender_way_point,
                'receiver_name' => $package->receiver_name,
                'receiver_address' => $package->receiver_address,
                'reveiver_way_point' => $package->receiver_way_point,
                'hash' => $package->hash,
                'code' => $package->code->content,
                'attachments' => $package->attachments,
                'items' => $package->items,
                'notes' => $notes
            ]
        ];

        $data = collect(array_merge($parentData, $items))->values()->toArray();

        return $data;
    }

    private static function checkHandling($handling = [])
    {
        $handling = Arr::wrap($handling);
        $packings = [];
        if ($handling !== []) {
            if (Arr::has($handling, 'type')) {
                $handling = array_column($handling, 'type');
            }

            // $handling = array_column($handling, 'type');
            foreach ($handling as $key => $packing) {

                if (Arr::has($packing, 'type')) {
                    $packing = array_column($handling, 'type');
                    $packings = $packing;
                }
            }
        }

        Log::info("Check handling", $packings);
        return $packings;
    }

    /**
     * get income bike service for partner
     */
    public static function getIncomeBikePartner($cc): int
    {
        switch ($cc) {
            case 150:
                $income = 200000;
                break;
            case 250:
                $income = 250000;
                break;
            case 999:
                $income = 350000;
                break;
            default:
                $income = 0;
                break;
        }

        return $income;
    }

    /**
     * Get income bike on dooring delivery of partner
     */
    public static function getIncomeBikeDooringPartner($cc): int
    {
        switch (true) {
            case $cc <= 250:
                $income = 150000;
                break;
            case $cc == 999:
                $income = 300000;
                break;
            default:
                $income = 0;
                break;
        }

        return $income;
    }

    /**
     * Partner get commision of cubic service
     */
    public static function cubicCalculate($items): float
    {
        $weightVolume = [];
        foreach ($items as $item) {
            $dimension = $item->height * $item->length * $item->width / 1000000;
            if ($dimension < 3) {
                $dimension = 3;
            }

            array_push($weightVolume, $dimension);
        }
        $cubic = array_sum($weightVolume);
        $cubicResult = floatval(number_format($cubic, 2, '.', ''));

        return $cubicResult;
    }
}
