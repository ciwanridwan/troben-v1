<?php

namespace App\Events\Partner\Transporter;

use App\Models\Partners\Transporter;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TransporterCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Transporter Instance
     *
     * @var App\Models\Partners\Transporter
     */

    public Transporter $transporter;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Transporter $transporter)
    {
        $this->transporter = $transporter;
    }
}
