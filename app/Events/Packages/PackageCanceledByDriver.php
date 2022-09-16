<?php

namespace App\Events\Packages;

use App\Models\Deliveries\Delivery;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Validation\ValidationException;

class PackageCanceledByDriver
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Delivery $delivery;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Delivery $delivery)
    {
        throw_if($delivery->status !== Delivery::STATUS_ACCEPTED, ValidationException::withMessages([
            'package' => __('delivery should be in '.Delivery::STATUS_ACCEPTED.' status'),
        ]));

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
