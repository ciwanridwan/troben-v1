<?php

namespace App\Events\Packages;

use App\Models\Packages\Package;
use App\Models\Deliveries\Delivery;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class PackageAttachedToDelivery
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var \App\Models\Packages\Package
     */
    public Package $package;

    /**
     * @var \App\Models\Deliveries\Delivery
     */
    public Delivery $delivery;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Package $package, Delivery $delivery)
    {
        $this->package = $package;
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
