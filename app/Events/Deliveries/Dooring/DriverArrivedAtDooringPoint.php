<?php

namespace App\Events\Deliveries\Dooring;

use App\Models\Deliveries\Delivery;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DriverArrivedAtDooringPoint
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var Delivery
     */
    public Delivery $delivery;

    /**
     * DriverArrivedAtDooringPoint constructor.
     * @param Delivery $delivery
     */
    public function __construct(Delivery $delivery)
    {
        $this->delivery = $delivery;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
