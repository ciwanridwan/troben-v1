<?php

namespace App\Listeners\Deliveries;

use App\Models\Deliveries\Delivery;
use App\Events\Deliveries\Pickup;

class UpdateDeliveryStatusByEvent
{
    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        switch (true) {
            case $event instanceof Pickup\PackageLoadedByDriver:
                $event->delivery->setAttribute('status', Delivery::STATUS_EN_ROUTE)->save();
                break;
            case $event instanceof Pickup\DriverUnloadedPackageInWarehouse:
                $event->delivery->setAttribute('status', Delivery::STATUS_FINISHED)->save();
                break;
        }
    }
}
