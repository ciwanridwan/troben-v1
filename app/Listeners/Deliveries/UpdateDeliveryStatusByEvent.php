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
     * @param object $event
     * @return void
     */
    public function handle(object $event)
    {
        switch (true) {
            case $event instanceof Pickup\PackageLoadedByDriver:
                $user = auth()->user();
                $event->delivery->setAttribute('type', Delivery::TYPE_PICKUP)->save();
                $event->delivery->setAttribute('status', Delivery::STATUS_EN_ROUTE)->save();
                $event->delivery->setAttribute('updated_by', $user->id)->save();
                break;
            case $event instanceof Pickup\DriverUnloadedPackageInWarehouse:
                $user = auth()->user();
                $event->delivery->setAttribute('type', Delivery::TYPE_PICKUP)->save();
                $event->delivery->setAttribute('status', Delivery::STATUS_FINISHED)->save();
                $event->delivery->setAttribute('updated_by', $user->id)->save();
                break;
            case $event instanceof Transit\DriverArrivedAtOriginWarehouse:
                $user = auth()->user();
                $event->delivery->setAttribute('type', Delivery::TYPE_TRANSIT)->save();
                $event->delivery->setAttribute('status', Delivery::STATUS_ACCEPTED)->save();
                $event->delivery->setAttribute('updated_by', $user->id)->save();
                break;
            case $event instanceof Transit\PackageLoadedByDriver:
                $user = auth()->user();
                $event->delivery->setAttribute('type', Delivery::TYPE_TRANSIT)->save();
                $event->delivery->setAttribute('status', Delivery::STATUS_EN_ROUTE)->save();
                $event->delivery->setAttribute('updated_by', $user->id)->save();
                break;
            case $event instanceof Transit\DriverUnloadedPackageInDestinationWarehouse:
                $user = auth()->user();
                $event->delivery->setAttribute('type', Delivery::TYPE_TRANSIT)->save();
                $event->delivery->setAttribute('status', Delivery::STATUS_FINISHED)->save();
                $event->delivery->setAttribute('updated_by', $user->id)->save();
                break;
            case $event instanceof Transit\WarehouseUnloadedPackage:
                $user = auth()->user();
                $event->delivery->setAttribute('type', Delivery::TYPE_TRANSIT)->save();
                $event->delivery->setAttribute('status', Delivery::STATUS_FINISHED)->save();
                $event->delivery->setAttribute('updated_by', $user->id)->save();
                break;
            case $event instanceof Dooring\DriverArrivedAtOriginPartner:
                $user = auth()->user();
                if ($event->delivery->transporter->partner->type === Partner::TYPE_TRANSPORTER) {
                    $event->delivery->setAttribute('type', Delivery::TYPE_DOORING)->save();
                    $event->delivery->setAttribute('status', Delivery::STATUS_ACCEPTED)->save();
                    $event->delivery->setAttribute('updated_by', $user->id)->save();
                }
                break;
            case $event instanceof Dooring\PackageLoadedByDriver:
                $user = auth()->user();
                $event->delivery->setAttribute('type', Delivery::TYPE_DOORING)->save();
                $event->delivery->setAttribute('status', Delivery::STATUS_EN_ROUTE)->save();
                $event->delivery->setAttribute('updated_by', $user->id)->save();

                /** @var Delivery $delivery */
                $delivery = $event->delivery;
                $delivery->packages->each(fn (Package $package) => $package->setAttribute('status', Package::STATUS_WITH_COURIER)->save());
                $delivery->packages->each(fn (Package $package) => $package->setAttribute('updated_by', $user->id)->save());

                break;
            case $event instanceof Dooring\DriverUnloadedPackageInDooringPoint:
                $user = auth()->user();
                /** @var Delivery $delivery */
                $delivery = $event->delivery;

                /** @var Package $package */
                $package = $event->package;
                $package->setAttribute('status', Package::STATUS_DELIVERED)->save();
                $package->setAttribute('updated_by', $user->id)->save();
                $delivery->packages()->where('id', $package->id)->updateExistingPivot($package, [
                    'is_onboard' => false,
                    'status' => Deliverable::STATUS_UNLOAD_BY_DESTINATION_PACKAGE,
                    'updated_by' => $user->id,
                ]);
                $delivery->setAttribute('updated_by', $user->id)->save();

                $item_codes = $package->item_codes->pluck('id');
                $scan_items = $delivery->code->scan_item_codes()->wherePivot('status', 'driver_dooring_load')->pluck('codes.id');

                $delivery->item_codes
                    ->whereIn('id', $scan_items)
                    ->whereIn('id', $item_codes)
                    ->each(function ($item_code) use ($delivery) {
                        $user = auth()->user();
                        $delivery->item_codes()->updateExistingPivot($item_code, [
                            'is_onboard' => false,
                            'status' => Deliverable::STATUS_UNLOAD_BY_DESTINATION_PACKAGE,
                            'updated_by' => $user->id,
                        ]);
                    });
                break;
        }
    }
}
