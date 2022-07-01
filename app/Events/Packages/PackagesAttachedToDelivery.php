<?php

namespace App\Events\Packages;

use App\Models\Deliveries\Delivery;
use Illuminate\Foundation\Events\Dispatchable;

class PackagesAttachedToDelivery
{
    use Dispatchable;

    /**
     * Delivery instance.
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
