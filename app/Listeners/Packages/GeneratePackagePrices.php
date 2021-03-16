<?php

namespace App\Listeners\Packages;

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
            // TODO : generate prices
        }
    }
}
