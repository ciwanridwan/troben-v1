<?php

namespace App\Events\Price;

use App\Models\Price;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PriceModificationFailed
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
