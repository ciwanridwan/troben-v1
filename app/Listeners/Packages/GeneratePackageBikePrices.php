<?php

namespace App\Listeners\Packages;

use App\Models\Packages\Item;
use App\Models\Packages\Price;
use App\Models\Packages\Package;
use App\Actions\Pricing\PricingCalculator;
use App\Casts\Package\Items\Handling;
use App\Events\Partners\PartnerCashierDiscountForBike;
use App\Models\Packages\Price as PackagePrice;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Jobs\Packages\Item\Prices\UpdateOrCreatePriceFromExistingItem;
use App\Jobs\Packages\UpdateOrCreatePriceFromExistingPackage;
use App\Models\Partners\Voucher;
use Illuminate\Validation\ValidationException;

class GeneratePackageBikePrices
{
    use DispatchesJobs;

    /**
     * Handle the event.
     *
     * @param  \App\Events\PackageBikeCreated  $event
     * @return void
     */
    public function handle(object $event)
    {
        if (property_exists($event, 'package') && $event->package instanceof Package && $event->package->payment_status !== Package::PAYMENT_STATUS_PAID) {
            $event->package->items->each(function (Item $item) use ($event) {
                // $existing_handling = [];
                // foreach (($item->handling ?? []) as $handling) {
                //     $handling['price'] = $item->qty * $handling['price'];
                //     $job = new UpdateOrCreatePriceFromExistingItem($event->package, $item, [
                //         'type' => Price::TYPE_HANDLING,
                //         'description' => $handling['type'],
                //         'amount' => $handling['price'],
                //     ]);
                //     $this->dispatch($job);

                //     $existing_handling[] = $handling['type'];
                // }

                // $item->prices()->where('type', Price::TYPE_HANDLING)
                //     ->whereNotIn('description', $existing_handling)
                //     ->delete();

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
                    'moto_cc' => $package->motoBikes()->first()->cc
                ];

                $result = PricingCalculator::getBikePrice($service_input['origin_regency_id'], $service_input['destination_id']);
                $cc = $service_input['moto_cc'];
                switch (true) {
                    case $cc <= 150:
                        $servicePrice = $result->lower_cc;
                        break;
                    case $cc === 250:
                        $servicePrice = $result->middle_cc;
                        break;
                    case $cc > 250:
                        $servicePrice = $result->high_cc;
                        break;
                    default: 
                        $servicePrice = 0;
                        break;
                }
                $package->setAttribute('tier_price', $service_input['moto_cc'])->save();
            } catch (ValidationException $e) {
                $servicePrice = 0;
            }

            $job = new UpdateOrCreatePriceFromExistingPackage($event->package, [
                'type' => Price::TYPE_SERVICE,
                'description' => Price::TYPE_SERVICE,
                'amount' => $servicePrice,
            ]);
            $this->dispatch($job);

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
            if ($event instanceof PartnerCashierDiscountForBike) {
                $is_approved = true;
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

            try {
                $ccInput = [
                    'moto_cc' => $package->motoBikes()->first()->cc
                ];
                switch (true) {
                    case $ccInput['moto_cc'] <= 150:
                        $handlingBikePrices = 175000;
                        break;
                    case $ccInput['moto_cc'] === 250:
                        $handlingBikePrices = 250000;
                        break;
                    case $ccInput['moto_cc'] > 250:
                        $handlingBikePrices = 450000;
                        break;
                    default:
                        $handlingBikePrices = 0;
                        break;
                }

                $job = new UpdateOrCreatePriceFromExistingPackage($event->package, [
                    'type' => Price::TYPE_HANDLING,
                    'description' => Handling::TYPE_BIKES,
                    'amount' => $handlingBikePrices,
                ]);
                $this->dispatch($job);

                /** Set Packate item id to bike handling */
                $item = $event->package->items()->first();
                $packagePrices = $event->package->prices()->where('type', Price::TYPE_HANDLING)->where('description', Price::DESCRIPTION_TYPE_BIKE)->first();
                $packagePrices->update(['package_item_id' => $item->id]);
            } catch (\Exception $e) {
                report($e);
            }
            
            $package->setAttribute('total_amount', PricingCalculator::getPackageTotalAmount($package, $is_approved))->save();
        }
    }
}
