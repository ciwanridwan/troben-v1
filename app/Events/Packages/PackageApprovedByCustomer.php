<?php

namespace App\Events\Packages;

use App\Models\Packages\Package;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PackageApprovedByCustomer
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var \App\Models\Packages\Package
     */
    public Package $package;

    /**
     * Create a new event instance.
     *
     * @param \App\Models\Packages\Package $package
     */
    public function __construct(Package $package)
    {
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