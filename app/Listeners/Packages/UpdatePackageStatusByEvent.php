<?php

namespace App\Listeners\Packages;

use App\Models\Packages\Package;
use App\Events\Deliveries\Pickup;
use App\Models\Deliveries\Delivery;
use App\Events\Packages\PackagePaymentVerified;
use App\Events\Packages\PackageCheckedByCashier;
use App\Events\Packages\WarehouseIsStartPacking;
use App\Events\Packages\PackageApprovedByCustomer;
use App\Events\Packages\PackageAttachedToDelivery;
use App\Events\Packages\PackageEstimatedByWarehouse;
use App\Events\Packages\WarehouseIsEstimatingPackage;
use App\Events\Packages\PackageAlreadyPackedByWarehouse;
use App\Events\Packages\PackageCanceledByAdmin;
use App\Events\Packages\PackageCanceledByCustomer;
use App\Events\Packages\PackageUpdated;
use App\Models\Customers\Customer;
use App\Events\Deliveries\Transit;

class UpdatePackageStatusByEvent
{
    /**
     * Handle the event.
     *
     * @param object $event
     * @return void
     */
    public function handle(object $event): void
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
            case $event instanceof Transit\DriverArrivedAtOriginWarehouse:
                $event->delivery->packages()
                    ->cursor()
                    ->each(fn (Package $package) => $event->delivery->type === Delivery::TYPE_TRANSIT && $package->setAttribute('status', Package::STATUS_IN_TRANSIT)->save())
                    ->each(fn (Package $package) => $package->pivot->setAttribute('is_onboard', true)->save());
                break;
            case $event instanceof WarehouseIsEstimatingPackage:
                $event->package->setAttribute('status', Package::STATUS_ESTIMATING);
                $event->package->estimator()->associate($event->actor);
                $event->package->save();
                break;
            case $event instanceof PackageEstimatedByWarehouse:
                $event->package->setAttribute('status', Package::STATUS_ESTIMATED)->save();
                break;
            case $event instanceof PackageCanceledByCustomer || $event instanceof PackageCanceledByAdmin:
                $event->package->setAttribute('status', Package::STATUS_CANCEL)->save();
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
            case $event instanceof PackagePaymentVerified:
                $event->package
                    ->setAttribute('status', Package::STATUS_WAITING_FOR_PACKING)
                    ->setAttribute('payment_status', Package::PAYMENT_STATUS_PAID)
                    ->save();
                break;
            case $event instanceof WarehouseIsStartPacking:
                $event->package->setAttribute('status', Package::STATUS_PACKING);
                $event->package->packager()->associate($event->actor);
                $event->package->save();
                break;
            case $event instanceof PackageAlreadyPackedByWarehouse:
                $event->package->setAttribute('status', Package::STATUS_PACKED)->save();
                break;
            case $event instanceof PackageAttachedToDelivery:
                if (in_array($event->package->status, [Package::STATUS_PACKED, Package::STATUS_IN_TRANSIT])) {
                    $event->package->setAttribute('status', Package::STATUS_MANIFESTED)->save();
                }
                break;
            case $event instanceof Transit\WarehouseUnloadedPackage:
                /** @var Package $package */
                $package = $event->package;
                $package->setAttribute('status', Package::STATUS_IN_TRANSIT)->save();
                break;
            case $event instanceof PackageUpdated:
                /** @var Package $package */
                $package = $event->package;
                $user = auth()->user();

                if ($package->customer->id === $user->id && $user instanceof Customer) {
                    $package->setAttribute('status', Package::STATUS_REVAMP)->save();
                }
                break;
        }
    }
}
