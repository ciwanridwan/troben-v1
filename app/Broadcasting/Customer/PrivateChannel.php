<?php

namespace App\Broadcasting\Customer;

use App\Models\Customers\Customer;

class PrivateChannel
{
    /**
     * Create a new channel instance.
     *
     * @return void
     */
    public function __construct(Customer $customer, array $message)
    {
        fcm()
            ->toTopic($customer->fcm_token) // $topic must an string (topic name)
            ->priority('normal')
            ->timeToLive(0)
            ->notification($message)
            ->send();
    }
}
