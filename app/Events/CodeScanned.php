<?php

namespace App\Events;

use App\Models\Code;
use App\Models\Deliveries\Delivery;
use App\Models\Partners\Pivot\UserablePivot;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CodeScanned
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Code $code;

    public Delivery $delivery;

    /**
     * @var string
     */
    public string $role;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Delivery $delivery, Code $code, string $role = UserablePivot::ROLE_DRIVER)
    {
        $this->delivery = $delivery;
        $this->code = $code;
        $this->role = $role;
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
