<?php

namespace App\Events\Payment\Nicepay;

use App\Models\Packages\Package;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewRegistrationVA
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var Package $package
     */
    public Package $package;

    /**
     * @var $response
     */
    public $response;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Package $package, $response)
    {
        $this->package = $package;
        $this->response = $response;
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
