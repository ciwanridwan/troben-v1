<?php

namespace App\Listeners\Deliveries;

use App\Events\Deliveries\Transit\DriverUnloadedPackageInDestinationWarehouse;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class UpdateDeliveryRoute
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        switch (true) {
            case $event instanceof DriverUnloadedPackageInDestinationWarehouse:
                $packages = $event->delivery->packages;
                foreach ($packages as $package) {
                    if (is_null($package->deliveryRoutes->reach_destination_1_at)) {
                        $package->deliveryRoutes()->update([
                            'reach_destination_1_at' => Carbon::now()
                        ]);
                    } elseif (is_null($package->deliveryRoutes->reach_destination_2_at)) {
                        $package->deliveryRoutes()->update([
                            'reach_destination_2_at' => Carbon::now()
                        ]);
                    } elseif (is_null($package->deliveryRoutes->reach_destination_3_at)) {
                        $package->deliveryRoutes()->update([
                            'reach_destination_3_at' => Carbon::now()
                        ]);
                    } else {
                        Log::info('Not ready to update delivery route');
                    }
                }
                Log::info('Driver finished at destination warehouse with update delivery route');
                break;
            default:
                # code...
                break;
        }
    }
}
