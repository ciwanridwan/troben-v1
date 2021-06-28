<?php

namespace App\Listeners\Deliveries;

use App\Events\Deliveries\Dooring;
use App\Events\Deliveries\Pickup;
use App\Events\Deliveries\Transit;
use App\Models\Deliveries\Deliverable;
use App\Models\Deliveries\Delivery;
use App\Models\Packages\Package;
use App\Models\Partners\Partner;
use Illuminate\Foundation\Bus\DispatchesJobs;

class UpdateDeliveryStatusByEvent
{
    use DispatchesJobs;
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
                $event->delivery->setAttribute('type', Delivery::TYPE_PICKUP)->save();
                $event->delivery->setAttribute('status', Delivery::STATUS_EN_ROUTE)->save();
                break;
            case $event instanceof Pickup\DriverUnloadedPackageInWarehouse:
                $event->delivery->setAttribute('type', Delivery::TYPE_PICKUP)->save();
                $event->delivery->setAttribute('status', Delivery::STATUS_FINISHED)->save();
                break;
            case $event instanceof Transit\DriverArrivedAtOriginWarehouse:
                $event->delivery->setAttribute('type', Delivery::TYPE_TRANSIT)->save();
                $event->delivery->setAttribute('status', Delivery::STATUS_ACCEPTED)->save();
                break;
            case $event instanceof Transit\PackageLoadedByDriver:
                $event->delivery->setAttribute('type', Delivery::TYPE_TRANSIT)->save();
                $event->delivery->setAttribute('status', Delivery::STATUS_EN_ROUTE)->save();
                break;
            case $event instanceof Transit\DriverUnloadedPackageInDestinationWarehouse:
                $event->delivery->setAttribute('type', Delivery::TYPE_TRANSIT)->save();
                $event->delivery->setAttribute('status', Delivery::STATUS_FINISHED)->save();
                break;
            case $event instanceof Dooring\DriverArrivedAtOriginPartner:
                if ($event->delivery->transporter->partner->type === Partner::TYPE_TRANSPORTER) {
                    $event->delivery->setAttribute('type', Delivery::TYPE_DOORING)->save();
                    $event->delivery->setAttribute('status', Delivery::STATUS_ACCEPTED)->save();
                }
                break;
            case $event instanceof Dooring\PackageLoadedByDriver:
                $event->delivery->setAttribute('type', Delivery::TYPE_DOORING)->save();
                $event->delivery->setAttribute('status', Delivery::STATUS_EN_ROUTE)->save();
                break;
            case $event instanceof Dooring\DriverUnloadedPackageInDooringPoint:
                /** @var Delivery $delivery */
                $delivery = $event->delivery;

                /** @var Package $package */
                $package = $event->package;

                $delivery->packages()->where('id', $package->id)->updateExistingPivot($package, ['status' => Deliverable::STATUS_UNLOAD_BY_DESTINATION_PACKAGE]);

                $scan_items = $delivery->code->scan_item_codes->pluck('id');
                $delivery->item_codes->whereIn('id', $scan_items)->each(function ($item_code) use ($delivery) {
                    $delivery->item_codes()->updateExistingPivot($item_code, ['status' => Deliverable::STATUS_UNLOAD_BY_DESTINATION_PACKAGE]);
                });
                break;
        }
    }
}
