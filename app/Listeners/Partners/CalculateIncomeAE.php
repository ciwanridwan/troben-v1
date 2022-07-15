<?php

namespace App\Listeners\Partners;

use App\Events\Deliveries\Dooring\DriverDooringFinished;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CalculateIncomeAE
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
     * @param  \App\Events\Deliveries\Dooring\DriverDooringFinished  $event
     * @return void
     */
    public function handle(DriverDooringFinished $event)
    {
        $q = "SELECT *
        FROM packages
        WHERE id IN (
            SELECT
            deliverable_id
            -- delivery_id, count(*) c
            FROM deliverables
            WHERE 1=1 AND
            delivery_id = 8994 AND
            is_onboard = false AND
            status = 'unload_by_destination_package' AND
            deliverable_type = 'App\Models\Packages\Package'
            -- GROUP BY delivery_id
            -- ORDER BY c DESC
        )";
    }
}
