<?php

namespace App\Events\Deliveries\Dooring;

use App\Models\Deliveries\Delivery;

class DriverDooringFinished
{
    /**
     * Delivery instances.
     *
     * @var Delivery $delivery
     */
    public Delivery $delivery;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Delivery $delivery)
    {
        $this->delivery = $delivery;
    }
}
