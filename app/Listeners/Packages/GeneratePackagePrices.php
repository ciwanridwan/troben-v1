<?php

namespace App\Listeners\Packages;

use App\Models\Packages\Item;
use App\Models\Packages\Price;
use App\Models\Packages\Package;
use App\Actions\Pricing\PricingCalculator;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Jobs\Packages\Item\Prices\UpdateOrCreatePriceFromExistingItem;
use App\Jobs\Packages\UpdateOrCreatePriceFromExistingPackage;
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
    public function handle($event)
    {
        if (property_exists($event, 'package') && $event->package instanceof Package && $event->package->payment_status !== Package::PAYMENT_STATUS_PAID) {
            $event->package->items->each(function (Item $item) use ($event) {
                foreach (($item->handling ?? []) as $handling) {
                    $job = new UpdateOrCreatePriceFromExistingItem($event->package, $item, [
                        'type' => Price::TYPE_HANDLING,
                        'description' => $handling['type'],
                        'amount' => $handling['price'],
                    ]);
                    $this->dispatch($job);
                }


                if ($item->is_insured) {
                    $insured_mul = 0.2 / 100; // 0.2%
                    $job = new UpdateOrCreatePriceFromExistingItem($event->package, $item, [
                        'type' => Price::TYPE_INSURANCE,
                        'description' => Price::TYPE_INSURANCE,
                        'amount' => $item->price * $insured_mul,
                    ]);
                    $this->dispatch($job);
                }
            });

            $package = $event->package;

            $package->refresh();

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

                $service_price = PricingCalculator::getServicePrice($service_input);
            } catch (ValidationException $e) {
                $service_price = 0;
            }

            $job = new UpdateOrCreatePriceFromExistingPackage($event->package, [
                'type' => Price::TYPE_SERVICE,
                'description' => Price::TYPE_SERVICE,
                'amount' => $service_price,
            ]);
            $this->dispatch($job);

            $package->setAttribute('total_amount', PricingCalculator::getPackageTotalAmount($package))->save();

            // todo : create service lainnya, contoh : biaya penjemputan
        }
    }
}
