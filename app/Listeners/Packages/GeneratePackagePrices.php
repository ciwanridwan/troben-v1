<?php

namespace App\Listeners\Packages;

use App\Models\Packages\Item;
use App\Models\Packages\Price;
use App\Models\Packages\Package;
use App\Actions\Pricing\PricingCalculator;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Jobs\Packages\Item\Prices\UpdateOrCreatePriceFromExistingItem;

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
            // TODO : map item to price
            $event->package->items->each(function (Item $item) use ($event) {
                // todo : create price type handling
                foreach (($item->handling ?? []) as $handling) {
                    $job = new UpdateOrCreatePriceFromExistingItem($event->package, $item, [
                        'type' => Price::TYPE_HANDLING,
                        'description' => $handling['type'],
                        'amount' => $handling['price'],
                    ]);
                    $this->dispatch($job);
                }

                // todo : create price type service, description item name
                // $job = new UpdateOrCreatePriceFromExistingItem($event->package, $item, [
                //     'type' => Price::TYPE_SERVICE,
                //     'description' => Price::TYPE_SERVICE,
                //     'amount' => PricingCalculator::getDimensionCharge(
                //         $event->package->origin_regency->origin_province_id,
                //         $event->package->origin_regency_id,
                //         $event->package->destination_sub_district_id,
                //         $item->height,
                //         $item->length,
                //         $item->width,
                //         $item->weight,
                //         $event->package->service_code
                //     )
                // ]);
                // $this->dispatch($job);

                // todo : create price type insurance, attach to item
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

            // todo : create service lainnya, contoh : biaya penjemputan
        }
    }
}
