<?php

namespace App\Events\Deliveries;

use App\Models\User;
use App\Models\Deliveries\Delivery;
use App\Models\Partners\Transporter;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use App\Models\Partners\Pivot\UserablePivot;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class DriverAssignedDooring
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var \App\Models\Deliveries\Delivery
     */
    public Delivery $delivery;

    /**
     * @var \App\Models\Partners\Transporter
     */
    public Transporter $transporter;

    public User $user;

    /**
     * Create a new event instance.
     *
     * @param \App\Models\Deliveries\Delivery $delivery
     * @param \App\Models\Partners\Pivot\UserablePivot $userablePivot
     */
    public function __construct(Delivery $delivery, UserablePivot $userablePivot)
    {
        $this->delivery = $delivery;
        $this->transporter = $userablePivot->userable;
        $this->user = $userablePivot->user;
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
