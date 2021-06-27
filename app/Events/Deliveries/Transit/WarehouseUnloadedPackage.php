<?php

namespace App\Events\Deliveries\Transit;

use App\Models\Code;
use App\Models\Deliveries\Delivery;
use App\Models\Packages\Package;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class WarehouseUnloadedPackage
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Delivery $delivery;

    public Package $package;

    /**
     * Create a new event instance.
     *
     * @return void
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
