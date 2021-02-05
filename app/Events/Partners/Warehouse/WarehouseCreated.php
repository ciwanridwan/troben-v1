<?php

namespace App\Events\Partners\Warehouse;

use App\Models\Partners\Warehouse;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WarehouseCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Instance Warehouse
     *
     * @var App\Models\Partners\Warehouse
     *
     */
    public Warehouse $warehouse;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Warehouse $warehouse)
    {
        $this->warehouse = $warehouse;
    }
}
