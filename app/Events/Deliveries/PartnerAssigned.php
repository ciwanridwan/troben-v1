<?php

namespace App\Events\Deliveries;

use App\Models\Deliveries\Delivery;
use App\Models\Partners\Partner;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Class PartnerAssigned.
 */
class PartnerAssigned
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var \App\Models\Deliveries\Delivery
     */
    public Delivery $delivery;

    /**
     * @var \App\Models\Partners\Partner
     */
    public Partner $partner;

    /**
     * @var User $user
     */
    public User $user;

    /**
     * PartnerAssigned constructor.
     * @param Delivery $delivery
     * @param Partner $partner
     */
    public function __construct(Delivery $delivery, Partner $partner)
    {
        $this->delivery = $delivery;
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
