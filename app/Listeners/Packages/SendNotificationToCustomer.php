<?php

namespace App\Listeners\Packages;

use App\Broadcasting\Customer\PrivateChannel;
use App\Events\Packages\PackageCheckedByCashier;
use App\Events\Payment\ListPaymentGateway;
use App\Models\Customers\Customer;
use App\Models\Notifications\Template;
use App\Models\Packages\Package;

class SendNotificationToCustomer
{
    public Package $package;

    public Customer $customer;

    public Template $notification;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        $this->package = $event->package;
        $this->customer = $event->package->customer;

        switch (true) {
            case $event instanceof PackageCheckedByCashier:
                $this->notification = Template::where('type', Template::TYPE_CUSTOMER_SHOULD_CONFIRM_ORDER)->first();
                break;
            case $event instanceof ListPaymentGateway:
                $this->notification = Template::where('type', Template::TYPE_CUSTOMER_SHOULD_PAY)->first();
                break;
            default:
                break;
        }

        new PrivateChannel($this->customer, $this->notification, ['package_code' => $this->package->code->content]);
    }
}
