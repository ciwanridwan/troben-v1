<?php

namespace App\Events\Packages;

use App\Broadcasting\Customer\PrivateChannel;
use App\Models\Customers\Customer;
use App\Models\Notifications\Template;
use App\Models\Packages\Package;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Validation\ValidationException;

class PackageCheckedByCashier
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Package $package;

    public Customer $customer;

    public Template $notification;

    /**
     * Create a new event instance.
     *
     * @param \App\Models\Packages\Package $package
     */
    public function __construct(Package $package)
    {
        $mustConditions = [Package::STATUS_ESTIMATED, Package::STATUS_REVAMP];
        throw_if(! in_array($package->status, $mustConditions), ValidationException::withMessages([
            'package' => __('package should be in '.implode(',', $mustConditions).' status'),
        ]));
        $this->package = $package;

        $this->customer = $package->customer;

        $this->notification = Template::where('type', Template::TYPE_CUSTOMER_SHOULD_CONFIRM_ORDER)->first();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel($this->customer, $this->notification, ['package_code' => $this->package->code->content]);
    }
}
