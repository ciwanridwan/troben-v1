<?php

namespace App\Events\Orders;

use App\Models\Orders\Item;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class OrderItemCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var Item|Collection
     */
    public $order_item;

    /**
     * @param Item|Collection $order_item
     */
    public function __construct($order_item)
    {
        $this->order_item = $order_item;
    }
}
