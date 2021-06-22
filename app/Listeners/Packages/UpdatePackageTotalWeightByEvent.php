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
    public function handle($event)
    {
        if (property_exists($event, 'package') && $event->package instanceof Package) {
            /** @var Package $package */
            $package = $event->package;
            $items = $package->items->toArray();
            $totalWeight = PricingCalculator::getTotalWeightBorne($items);
            $package->setAttribute('total_weight', $totalWeight)->save();
        }
    }
}
