<?php

namespace App\Events\Packages;

use App\Models\Packages\Package;
use App\Models\Partners\Partner;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class PartnerAssigned
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Package $package;

    public Partner $partner;

    /**
     * Create a new event instance.
     *
     * @param \App\Models\Packages\Package $package
     * @param \App\Models\Partners\Partner $partner
     */
    public function __construct(Package $package, Partner $partner)
    {
        $this->package = $package;
        $this->partner = $partner;
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
