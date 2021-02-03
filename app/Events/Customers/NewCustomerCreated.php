<?php

namespace App\Events\Customers;

use App\Models\Customers\Customer;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class NewCustomerCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Customer instance.
     *
     * @var \App\Models\Customers\Customer
     */
    public Customer $customer;

    /**
     * Create a new event instance.
     *
     * @param \App\Models\Customers\Customer $customer
     */
    public function __construct(Customer $customer)
    {
        $this->customer = $customer;
    }
}
