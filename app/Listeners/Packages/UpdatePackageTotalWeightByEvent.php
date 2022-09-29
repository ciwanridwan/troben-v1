<?php

namespace App\Listeners\Packages;

use App\Actions\Pricing\PricingCalculator;
use App\Models\Packages\Package;

class UpdatePackageTotalWeightByEvent
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(object $event): void
    {
        if (property_exists($event, 'package') && $event->package instanceof Package) {
            /** @var Package $package */
            $package = $event->package->refresh();

            $items = [];
            foreach ($package->items as $item) {
                $items[] = [
                    'weight' => $item->weight,
                    'height' => $item->height,
                    'length' => $item->length,
                    'width' => $item->width,
                    'qty' => $item->qty,
                    'handling' => ! empty($item->handling) ? array_column($item->handling, 'type') : null
                ];
            }

            $totalWeight = PricingCalculator::getTotalWeightBorne($items, $package->service_code);
            $package->setAttribute('total_weight', $totalWeight)->save();
        }
    }
}
