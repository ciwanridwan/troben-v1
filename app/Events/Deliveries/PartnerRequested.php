<?php

namespace App\Events\Deliveries;

use App\Models\Deliveries\Delivery;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Class PartnerRequested.
 */
class PartnerRequested
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var Delivery $delivery
     */
    public Delivery $delivery;

    /**
     * PartnerRequested constructor.
     * @param Delivery $delivery
     */
    public function __construct(Delivery $delivery)
    {
        $this->delivery = $delivery;
    }

    /**
     * @return PrivateChannel
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
