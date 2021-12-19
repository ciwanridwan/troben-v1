<?php

namespace App\Events\Deliveries\Transit;

use App\Models\Deliveries\Delivery;
use Illuminate\Foundation\Events\Dispatchable;

class WarehouseUnloadedPackages
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
