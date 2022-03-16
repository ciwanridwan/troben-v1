<?php

namespace App\Listeners\Packages;

use App\Events\Payment\Nicepay\PayByNicepay;
use App\Events\Payment\Nicepay\Registration;
use App\Exceptions\Error;
use App\Http\Response;
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
use App\Models\User;

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
                $user = auth()->user();
                $event->delivery->packages()
                    ->cursor()
                    ->each(fn (Package $package) => $event->delivery->type === Delivery::TYPE_PICKUP && $package->setAttribute('status', Package::STATUS_PICKED_UP)->setAttribute('updated_by', $user->id)->save())
                    ->each(fn (Package $package) => $package->pivot->setAttribute('is_onboard', true)->save());
                break;
            case $event instanceof Pickup\DriverUnloadedPackageInWarehouse:
                $user = auth()->user();
                $event->delivery->packages()
                    ->cursor()
                    ->each(fn (Package $package) => $event->delivery->type === Delivery::TYPE_PICKUP && $package->setAttribute('status', Package::STATUS_WAITING_FOR_ESTIMATING)->setAttribute('updated_by', $user->id)->save())
                    ->each(fn (Package $package) => $package->pivot->setAttribute('is_onboard', false)->save());
                break;
            case $event instanceof Transit\DriverArrivedAtOriginWarehouse:
                $user = auth()->user();
                $event->delivery->packages()
                    ->cursor()
                    ->each(fn (Package $package) => $event->delivery->type === Delivery::TYPE_TRANSIT && $package->setAttribute('status', Package::STATUS_IN_TRANSIT)->setAttribute('updated_by', $user->id)->save())
                    ->each(fn (Package $package) => $package->pivot->setAttribute('is_onboard', true)->save());
                break;
            case $event instanceof WarehouseIsEstimatingPackage:
                $user = auth()->user();
                $event->package->setAttribute('status', Package::STATUS_ESTIMATING);
                $event->package->setAttribute('updated_by', $user->id);
                $event->package->estimator()->associate($event->actor);
                $event->package->save();
                break;
            case $event instanceof PackageEstimatedByWarehouse:
                $user = auth()->user();
                $event->package->setAttribute('status', Package::STATUS_ESTIMATED)
                    ->setAttribute('updated_by', $user->id)
                    ->save();
                break;
            case $event instanceof PackageCanceledByCustomer || $event instanceof PackageCanceledByAdmin:
                $event->package->setAttribute('status', Package::STATUS_CANCEL)->save();
                $event->package->setAttribute('updated_by', User::USER_SYSTEM_ID);
                break;
            case $event instanceof PackageCheckedByCashier:
                $user = auth()->user();
                $event->package->setAttribute('status', Package::STATUS_WAITING_FOR_APPROVAL)
                    ->setAttribute('updated_by', $user->id)
                    ->save();
                break;
            case $event instanceof PackageApprovedByCustomer:
                $event->package
                    ->setAttribute('status', Package::STATUS_ACCEPTED)
                    ->setAttribute('payment_status', Package::PAYMENT_STATUS_PENDING)
                    ->setAttribute('updated_by', User::USER_SYSTEM_ID)
                    ->save();
                $event->package->setAttribute('updated_by', User::USER_SYSTEM_ID)->save();
                break;
            case $event instanceof PackagePaymentVerified:
                $event->package
                    ->setAttribute('status', Package::STATUS_WAITING_FOR_PACKING)
                    ->setAttribute('payment_status', Package::PAYMENT_STATUS_PAID)
                    ->setAttribute('updated_by', User::USER_SYSTEM_ID)
                    ->save();
                break;
            case $event instanceof WarehouseIsStartPacking:
                $user = auth()->user();
                $event->package->setAttribute('status', Package::STATUS_PACKING)
                    ->setAttribute('updated_by', $user->id);
                $event->package->packager()->associate($event->actor);
                $event->package->save();
                break;
            case $event instanceof PackageAlreadyPackedByWarehouse:
                $user = auth()->user();
                $event->package->setAttribute('status', Package::STATUS_PACKED)
                    ->setAttribute('updated_by', $user->id)
                    ->save();
                break;
            case $event instanceof PackageAttachedToDelivery:
                $user = auth()->user();
                if (in_array($event->package->status, [Package::STATUS_PACKED, Package::STATUS_IN_TRANSIT])) {
                    $event->package->setAttribute('status', Package::STATUS_MANIFESTED)
                        ->setAttribute('updated_by', $user->id)
                        ->save();
                }
                break;
            case $event instanceof Transit\WarehouseUnloadedPackage:
                $user = auth()->user();
                /** @var Package $package */
                $package = $event->package;
                $package->setAttribute('status', Package::STATUS_IN_TRANSIT)
                    ->setAttribute('updated_by', $user->id)
                    ->save();
                break;
            case $event instanceof PackageUpdated:
                /** @var Package $package */
                $package = $event->package;
                $user = auth()->user();
                if ($package->customer->id === $user->id && $user instanceof Customer) {
                    $package->setAttribute('status', Package::STATUS_REVAMP)
                        ->setAttribute('updated_by', $user->id)
                        ->save();
                }
                break;
            case $event instanceof Registration\NewVacctRegistration:
                /** @var Package $package */
                $package = $event->package;
                $package->setAttribute('payment_status', Package::PAYMENT_STATUS_PENDING)
                    ->setAttribute('updated_by', User::USER_SYSTEM_ID)->save();
                break;
            case $event instanceof Registration\NewQrisRegistration:
                /** @var Package $package */
                $package = $event->package;
                $package->setAttribute('payment_status', Package::PAYMENT_STATUS_PENDING)
                    ->setAttribute('updated_by', User::USER_SYSTEM_ID)->save();
                break;
            case $event instanceof PayByNicepay:
                $params = $event->params;
                $package = $event->package;

                throw_if($params->status !== '0', Error::make(Response::RC_PAYMENT_NOT_PAID));
                if ($params->status === '0' && $package->payment_status !== Package::PAYMENT_STATUS_PAID) {
                    $package->setAttribute('payment_status', Package::PAYMENT_STATUS_PAID);
                    $package->setAttribute('status', Package::STATUS_WAITING_FOR_PACKING);
                    $package->setAttribute('updated_by', User::USER_SYSTEM_ID);
                    $package->save();
                }
                break;
        }
    }
}
