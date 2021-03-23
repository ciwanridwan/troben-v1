<?php

namespace App\Events\Deliveries;

use App\Models\Deliveries\Delivery;
use App\Models\Partners\Transporter;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TransporterAssigned
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var \App\Models\Partners\Transporter
     */
    public Transporter $transporter;

    /**
     * @var \App\Models\Deliveries\Delivery
     */
    public Delivery $delivery;

    /**
     * Create a new event instance.
     *
     * @param \App\Models\Partners\Transporter $transporter
     * @param \App\Models\Deliveries\Delivery $delivery
     */
    public function __construct(Transporter $transporter, Delivery $delivery)
    {
        $this->transporter = $transporter;
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
