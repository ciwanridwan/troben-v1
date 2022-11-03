<?php

namespace App\Listeners\Partners;

use App\Events\Deliveries\Dooring\DriverDooringFinished;
use App\Models\Partners\AgentProfitAE;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class CalculateIncomeAEIndirect
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
        // ignore parameter
        $delivery = $event->delivery;
        $deliveryId = $delivery->getKey();

        Artisan::call('tb:income');
    }
}
