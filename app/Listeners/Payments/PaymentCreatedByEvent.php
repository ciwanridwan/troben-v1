<?php

namespace App\Listeners\Payments;

use App\Models\Deliveries\Delivery;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class PaymentCreatedByEvent
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
        $delivery = $event->delivery instanceof Delivery?$event->delivery:;
    }
}
