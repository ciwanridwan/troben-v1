<?php

namespace App\Listeners\Packages;

use App\Models\Packages\Item;
use App\Models\Packages\Package;

class GeneratePackagePrices
{
    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        if (property_exists($event, 'package') && $event->package instanceof Package) {
            // TODO : map item to price
            $event->package->items->each(function (Item $item) {
                // todo : create price type service, description item name

                // todo : create price type insurance, attach to item
            });

            // todo : create service lainnya, contoh : biaya penjemputan
        }
    }
}
