<?php

namespace App\Events\Customers;

use App\Models\Customers\Address;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

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
        $this->customer_address = $customer_address;
    }
}
