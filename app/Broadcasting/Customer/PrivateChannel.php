<?php

namespace App\Broadcasting\Customer;

use App\Abstracts\TrawlNotification;
use App\Models\Customers\Customer;
use App\Models\Notifications\Notification;
use App\Models\Notifications\Template;

class PrivateChannel extends TrawlNotification
{
    /**
     * @param Customer $customer
     * @param Template $notification
     * @param array $data
     */
    public function __construct(Customer $customer, Template $notification, array $data = [])
    {
        $this->customer = $customer;
        $this->notification = $notification;
        $this->data = $data;
        $this
            ->validateData()
            ->recordLog()
            ->push();
    }

    /**
     * Store notification to notifiables table on database.
     *
     * @return $this
     */
    public function recordLog(): self
    {
        $this->customer->notifications()->save((new Notification())->setAttribute('data',$this->template));
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
