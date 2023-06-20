<?php

namespace App\Listeners\Packages;

use App\Events\Partners\PartnerCashierDiscount;
use App\Models\Packages\Item;
use App\Models\Packages\Price;
use App\Models\Packages\Package;
use App\Actions\Pricing\PricingCalculator;
use App\Models\Packages\Price as PackagePrice;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Jobs\Packages\Item\Prices\UpdateOrCreatePriceFromExistingItem;
use App\Jobs\Packages\UpdateOrCreatePriceFromExistingPackage;
use App\Models\Partners\Voucher;
use App\Models\Service;
use Illuminate\Validation\ValidationException;

class GeneratePackagePrices
{
    use DispatchesJobs;

    /**
     * Handle the event.
     *
     * @param object $event
     * @return void
     * @throws \Illuminate\Validation\ValidationException
     */
    public function handle(object $event)
    {
        if (property_exists($event, 'package') && $event->package instanceof Package && $event->package->payment_status !== Package::PAYMENT_STATUS_PAID) {
            $event->package->items->each(function (Item $item) use ($event) {
                $existing_handling = [];
                foreach (($item->handling ?? []) as $handling) {
                    $handling['price'] = $item->qty * $handling['price'];
                    $job = new UpdateOrCreatePriceFromExistingItem($event->package, $item, [
                        'type' => Price::TYPE_HANDLING,
                        'description' => $handling['type'],
                        'amount' => $handling['price'],
                    ]);
                    $this->dispatch($job);

                    $existing_handling[] = $handling['type'];
                }

                $item->prices()->where('type', Price::TYPE_HANDLING)
                    ->whereNotIn('description', $existing_handling)
                    ->delete();

                if ($item->is_insured) {
                    $insured_mul = 0.2 / 100; // 0.2%
                    $job = new UpdateOrCreatePriceFromExistingItem($event->package, $item, [
                        'type' => Price::TYPE_INSURANCE,
                        'description' => Price::TYPE_INSURANCE,
                        'amount' => $item->price * $insured_mul,
                    ]);
                    $this->dispatch($job);
                } else {
                    $item->prices()->where('type', Price::TYPE_INSURANCE)->delete();
                }
            });

            /** @var Package $package */
            $package = $event->package->refresh();

            if (!$package->relationLoaded('origin_regency')) {
                $package->load('origin_regency');
            }

            if (!$package->relationLoaded('destination_sub_district')) {
                $package->load('destination_sub_district');
            }

            try {
                $service_input = [
                    'origin_province_id' => $package->origin_regency->province_id,
                    'origin_regency_id' => $package->origin_regency->id,
                    'destination_id' => $package->destination_sub_district->id,
                    'items' => $package->items->toArray()
                ];

                $serviceCode = $package->service_code;

                switch ($serviceCode) {
                    case Service::TRAWLPACK_STANDARD:
                        $service_price = PricingCalculator::getServicePrice($service_input);
                        break;
                    case Service::TRAWLPACK_CUBIC:
                        $service_price = PricingCalculator::getServiceCubicPrice($service_input);
                        break;
                    case Service::TRAWLPACK_EXPRESS:
                        $service_price = PricingCalculator::getServiceExpressPrice($service_input);
                        break;
                }
            } catch (ValidationException $e) {
                $service_price = 0;
            }

            switch ($serviceCode) {
                case Service::TRAWLPACK_STANDARD:
                    $job = new UpdateOrCreatePriceFromExistingPackage($event->package, [
                        'type' => Price::TYPE_SERVICE,
                        'description' => Price::TYPE_SERVICE,
                        'amount' => $service_price,
                    ]);
                    $this->dispatch($job);
                    break;
                case Service::TRAWLPACK_CUBIC:
                    $job = new UpdateOrCreatePriceFromExistingPackage($event->package, [
                        'type' => Price::TYPE_SERVICE,
                        'description' => Price::DESCRIPTION_TYPE_CUBIC,
                        'amount' => $service_price,
                    ]);
                    $this->dispatch($job);
                    break;
                case Service::TRAWLPACK_EXPRESS:
                    $job = new UpdateOrCreatePriceFromExistingPackage($event->package, [
                        'type' => Price::TYPE_SERVICE,
                        'description' => Price::DESCRIPTION_TYPE_EXPRESS,
                        'amount' => $service_price,
                    ]);

                    $this->dispatch($job);
                    break;
            }

            // Set Additional Price
            if ($package->prices) {
                $serviceCode = $package->service_code;
                $items = $package->items->toArray();

                $additionalPrice = PricingCalculator::getAdditionalPrices($items, $serviceCode);

                $job = new UpdateOrCreatePriceFromExistingPackage($package, [
                    'type' => Price::TYPE_SERVICE,
                    'description' => Price::TYPE_ADDITIONAL,
                    'amount' => $additionalPrice,
                ]);
                $this->dispatch($job);
            }

            // platform fee
            if ($package->prices) {
                $job = new UpdateOrCreatePriceFromExistingPackage($package, [
                    'type' => Price::TYPE_PLATFORM,
                    'description' => Price::DESCRIPTION_PLATFORM_FEE,
                    'amount' => Price::FEE_PLATFORM,
                ]);
                $this->dispatch($job);
            }

            $is_approved = false;

            // generate discount if using promotion code
            if ($package->claimed_promotion != null) {
                $service = $package->prices()->where('type', Price::TYPE_SERVICE)->first();
                if ($package->total_weight <= $package->claimed_promotion->promotion->max_weight) {
                    $discount_amount = $service->amount;
                } else {
                    $discount_amount = $package->tier_price * $package->claimed_promotion->promotion->max_weight;
                }

                $job = new UpdateOrCreatePriceFromExistingPackage($package, [
                    'type' => Price::TYPE_DISCOUNT,
                    'description' => Price::TYPE_SERVICE,
                    'amount' => $discount_amount,
                ]);
                $this->dispatch($job);
            }
            if ($package->claimed_voucher != null) {
                $service = $package->prices()->where('description', PackagePrice::TYPE_SERVICE)->where('type', PackagePrice::TYPE_SERVICE)->get()->sum('amount');
                $pickup = $package->prices()->where('description', PackagePrice::TYPE_PICKUP)->where('type', PackagePrice::TYPE_DELIVERY)->get()->sum('amount');

                $discount = [
                    'type' => Price::TYPE_DISCOUNT,
                    'description' => Price::TYPE_SERVICE,
                    'amount' => 0,
                ];

                if ($package->claimed_voucher->voucher && $package->claimed_voucher->voucher->aevoucher) {
                    $t = $package->claimed_voucher->voucher->type;
                    $av = $package->claimed_voucher->voucher->aevoucher;
                    if ($t == Voucher::VOUCHER_FREE_PICKUP) {
                        $discount['description'] = Price::TYPE_PICKUP;
                        $discount['amount'] = $pickup;
                    }
                    if ($t == Voucher::VOUCHER_DISCOUNT_SERVICE_PERCENTAGE) {
                        $amount = $service * ($av->discount / 100);
                        $discount['amount'] = $amount;
                    }
                    if ($t == Voucher::VOUCHER_DISCOUNT_SERVICE_NOMINAL) {
                        if ($av->nominal > $service) {
                            $av->nominal = $service;
                        }
                        $discount['amount'] = $av->nominal;
                    }
                } else { // regular voucher
                    $discount['amount'] = $service * ($package->claimed_voucher->discount / 100);
                }

                $job = new UpdateOrCreatePriceFromExistingPackage($package, $discount);
                $this->dispatch($job);
                $is_approved = true;
            }
            
            if ($event instanceof PartnerCashierDiscount) {
                $is_approved = true;
            }

            $package->setAttribute('total_amount', PricingCalculator::getPackageTotalAmount($package, $is_approved))->save();

            try {
                switch ($serviceCode) {
                    case Service::TRAWLPACK_STANDARD:
                        $origin_regency = $package->origin_regency;
                        $price = PricingCalculator::getPrice($origin_regency->province_id, $origin_regency->id, $package->destination_sub_district_id);
                        $tier = PricingCalculator::getTier($price, $package->total_weight);
                        $package->setAttribute('tier_price', $tier)->save();
                        break;
                    case Service::TRAWLPACK_CUBIC:
                        $origin_regency = $package->origin_regency;
                        $price = PricingCalculator::getCubicPrice($origin_regency->province_id, $origin_regency->id, $package->destination_sub_district_id);
                        $tier = $price->amount;
                        $package->setAttribute('total_weight', 0)->save();
                        $package->setAttribute('tier_price', $tier)->save();
                        break;
                    case Service::TRAWLPACK_EXPRESS:
                        $origin_regency = $package->origin_regency;
                        $price = PricingCalculator::getExpressPrice($origin_regency->province_id, $origin_regency->id, $package->destination_sub_district_id);
                        $tier = $price->amount;
                        $package->setAttribute('tier_price', $tier)->save();
                        break;
                }
            } catch (\Throwable $th) {
                throw $th;
            }
            // todo : create service lainnya, contoh : biaya penjemputan
        }
    }
}
