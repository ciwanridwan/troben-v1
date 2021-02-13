<?php

namespace App\Events\Orders;

use App\Models\Order;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class OrderCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;


    /**
     * @var Order
     */
    public Order $order;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }
}
