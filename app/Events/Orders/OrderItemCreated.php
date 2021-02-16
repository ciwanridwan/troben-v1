<?php

namespace App\Events\Orders;

use App\Models\Orders\Item;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

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
