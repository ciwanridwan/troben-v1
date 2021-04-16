<?php

namespace App\Events\Deliveries\Deliverable;

use App\Models\Deliveries\Deliverable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeliverableItemCodeUpdate
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Deliverable $deliverable;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Deliverable $deliverable)
    {
        $this->deliverable = $deliverable;
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
