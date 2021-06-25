<?php

namespace App\Events\Deliveries\Dooring;

use App\Models\Deliveries\Delivery;
use App\Models\Packages\Package;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DriverUnloadedPackageInDooringPoint
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var Delivery $delivery
     */
    public Delivery $delivery;

    /**
     * @var Package $package
     */
    public Package $package;

    /**
     * DriverUnloadedPackageInDooringPoint constructor.
     * @param Delivery $delivery
     * @param Package $package
     */
    public function __construct(Delivery $delivery, Package $package)
    {
        $this->delivery = $delivery;
        $this->package = $package;
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
