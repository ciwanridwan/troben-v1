<?php

namespace App\Listeners\Packages;

use App\Events\Deliveries\Pickup;
use App\Models\Packages\Package;

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
                    ->each(fn(Package $package) =>
                        $package->setAttribute('status', Package::STATUS_WITH_COURIER)->save() ||
                        $event->delivery->packages()->updateExistingPivot($package, ['is_onboard' => true]));
                break;
            case $event instanceof Pickup\DriverUnloadedPackageInWarehouse:
                $event->delivery->packages()
                    ->cursor()
                    ->each(fn(Package $package) =>
                        $package->setAttribute('status', Package::STATUS_ESTIMATING)->save() ||
                        $event->delivery->packages()->updateExistingPivot($package, ['is_onboard' => false]));
                break;
        }
    }
}
