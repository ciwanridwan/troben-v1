<?php

namespace App\Events\Price;

use App\Models\Price;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class PriceModified
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Price instance.
     *
     * @var \App\Models\Price
     */
    public Price $price;

    /**
     * Event Create New Price.
     *
     * @param \App\Models\Price $price
     */
    public function __construct(Price $price)
    {
        $this->price = $price;
    }
}
