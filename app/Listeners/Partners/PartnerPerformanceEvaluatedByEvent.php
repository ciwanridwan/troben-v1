<?php

namespace App\Listeners\Partners;

use App\Events\Deliveries\Transit\DriverUnloadedPackageInDestinationWarehouse;
use App\Events\Deliveries\Transit\WarehouseUnloadedPackages;
use App\Models\Packages\Package;
use App\Models\Partners\Performances\PerformanceModel;
use Carbon\Carbon;

class PartnerPerformanceEvaluatedByEvent
{
    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(object $event): void
    {
        switch (true) {
            case $event instanceof DriverUnloadedPackageInDestinationWarehouse:
                $delivery = $event->delivery;
                $packages = $delivery->packages;

                $attributes = [
                    'reached_at' => Carbon::now(),
                    'status' => PerformanceModel::STATUS_REACHED
                ];
                foreach ($packages as $package) {
                    /** @var Package $package */
                    $package->partner_performance->update($attributes);
                }

                $delivery->partner_performance->update($attributes);
                break;
            case $event instanceof WarehouseUnloadedPackages:
                $delivery = $event->delivery;

                $delivery->partner_performance->update([
                    'reached_at' => Carbon::now(),
                    'status' => PerformanceModel::STATUS_REACHED
                ]);
                break;
        }
    }
}
