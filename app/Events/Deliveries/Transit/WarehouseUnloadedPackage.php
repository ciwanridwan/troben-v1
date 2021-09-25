<?php

namespace App\Events\Deliveries\Transit;

use App\Models\Deliveries\Delivery;
use App\Models\Packages\Package;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WarehouseUnloadedPackage
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Delivery $delivery;

    public Package $package;

    public string $role;

    /**
     * Event when warehouse unloaded package.
     *
     * @param Delivery $delivery
     * @param Package $package
     * @param string $role
     */
    public function __construct(Delivery $delivery, Package $package, string $role)
    {
        $this->delivery = $delivery;
        $this->package = $package;
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
