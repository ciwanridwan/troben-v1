<?php

namespace App\Listeners\Packages;

use App\Events\Packages\PackageCheckedByCashier;
use App\Models\Packages\Package;
use App\Events\Deliveries\Pickup;
use App\Models\Deliveries\Delivery;
use App\Events\Packages\PackageEstimatedByWarehouse;
use App\Events\Packages\WarehouseIsEstimatingPackage;

class UpdatePackageStatusByEvent
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
                $event->delivery->packages()
                    ->cursor()
                    ->each(fn (Package $package) => $event->delivery->type === Delivery::TYPE_PICKUP && $package->setAttribute('status', Package::STATUS_PICKED_UP)->save())
                    ->each(fn (Package $package) => $package->pivot->setAttribute('is_onboard', true)->save());
                break;
            case $event instanceof Pickup\DriverUnloadedPackageInWarehouse:
                $event->delivery->packages()
                    ->cursor()
                    ->each(fn (Package $package) => $event->delivery->type === Delivery::TYPE_PICKUP && $package->setAttribute('status', Package::STATUS_WAITING_FOR_ESTIMATING)->save())
                    ->each(fn (Package $package) => $package->pivot->setAttribute('is_onboard', false)->save());
                break;
            case $event instanceof WarehouseIsEstimatingPackage:
                $event->package->setAttribute('status', Package::STATUS_ESTIMATING);
                $event->package->estimator()->associate($event->actor);
                $event->package->save();
                break;
            case $event instanceof PackageEstimatedByWarehouse:
                $event->package->setAttribute('status', Package::STATUS_ESTIMATED)->save();
                break;
            case $event instanceof PackageCheckedByCashier:
                $event->package->setAttribute('status', Package::STATUS_WAITING_FOR_APPROVAL)->save();
                break;
        }
    }
}
