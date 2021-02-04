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

class CustomerAddressCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Address $customer_address;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Address $customer_address)
    {
        //
    }
}
