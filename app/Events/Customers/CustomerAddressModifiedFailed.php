<?php

namespace App\Events\Customers;

use App\Models\Customers\Address;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CustomerAddressModifiedFailed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Customer instance.
     *
     * @var \App\Models\Customers\Customer
     */
    public Address $address;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Address $address)
    {
        $this->$address = $address;
    }
}
