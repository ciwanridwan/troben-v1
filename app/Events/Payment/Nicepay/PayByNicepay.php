<?php

namespace App\Events\Payment\Nicepay;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;

class PayByNicepay
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** @var Request $params */
    public Request $params;

    /**
     * PayByNicepay constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->params = $request;
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
