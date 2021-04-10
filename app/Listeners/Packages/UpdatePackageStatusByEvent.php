<?php

namespace App\Listeners\Packages;

use App\Models\Packages\Package;
use App\Events\Deliveries\Pickup;
use App\Models\Deliveries\Delivery;
use App\Events\Packages\PackageCheckedByCashier;
use App\Events\Packages\WarehouseIsStartPacking;
use App\Events\Packages\PackageApprovedByCustomer;
use App\Events\Packages\PackageEstimatedByWarehouse;
use App\Events\Packages\WarehouseIsEstimatingPackage;
use App\Events\Packages\PackagePaymentVerifiedByAdmin;
use App\Events\Packages\PackageAlreadyPackedByWarehouse;

class UpdatePackageStatusByEvent
{
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
            case $event instanceof PackageApprovedByCustomer:
                $event->package
                    ->setAttribute('status', Package::STATUS_ACCEPTED)
                    ->setAttribute('payment_status', Package::PAYMENT_STATUS_PENDING)
                    ->save();
                break;
            case $event instanceof PackagePaymentVerifiedByAdmin:
                $event->package->setAttribute('payment_status', Package::PAYMENT_STATUS_PAID)->save();
                break;
            case $event instanceof WarehouseIsStartPacking:
                $event->package->setAttribute('status', Package::STATUS_PACKING);
                $event->package->packager()->associate($event->actor);
                $event->package->save();
                break;
            case $event instanceof PackageAlreadyPackedByWarehouse:
                $event->package->setAttribute('status', Package::STATUS_PACKED)->save();
                break;
        }
    }
}
