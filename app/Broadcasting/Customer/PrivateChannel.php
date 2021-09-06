<?php

namespace App\Broadcasting\Customer;

use App\Abstracts\TrawlNotification;
use App\Models\Customers\Customer;
use App\Models\Notifications\Notification;

class PrivateChannel extends TrawlNotification
{
    /**
     * @param Customer $customer
     * @param Notification $notification
     * @param array $data
     */
    public function __construct(Customer $customer, Notification $notification, array $data = [])
    {
        $this->customer = $customer;
        $this->notification = $notification;
        $this->data = $data;
        $this
            ->recordLog()
            ->validateData()
            ->push();
    }

    /**
     * Store notification to notifiables table on database.
     *
     * @return $this
     */
    public function recordLog(): self
    {
        $this->notification->customers()->attach($this->customer->id);
        return $this;
    }

    /**
     * Push notification to customer.
     */
    public function push(): void
    {
        fcm()
            ->toTopic($this->customer->fcm_token)
            ->priority($this->notification->priority)
            ->timeToLive(0)
            ->notification($this->template)
            ->send();
    }

}
