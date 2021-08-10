<?php

namespace App\Events\Payment\Nicepay\Registration;

use App\Models\Packages\Package;
use App\Models\Payments\Gateway;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewVacctRegistration
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var Package $package
     */
    public Package $package;

    /**
     * @var Gateway $gateway
     */
    public Gateway $gateway;

    /**
     * @var object $response
     */
    public object $response;

    /**
     * NewVacctRegistration constructor.
     * @param Package $package
     * @param $response
     */
    public function __construct(Package $package, Gateway $gateway, $response)
    {
        $this->package = $package;
        $this->gateway = $gateway;
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
