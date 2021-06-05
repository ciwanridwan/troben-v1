<?php

namespace App\Events\Packages;

use App\Models\Packages\Package;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Validation\ValidationException;

class PackageCanceledByCustomer
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Package $package;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Package $package)
    {
        throw_if($package->status !== Package::STATUS_WAITING_FOR_APPROVAL || $package->payment_status !== Package::PAYMENT_STATUS_DRAFT, ValidationException::withMessages([
            'package' => __('package should be in ' . Package::STATUS_WAITING_FOR_APPROVAL . ' status and payment status ' . Package::PAYMENT_STATUS_DRAFT),
        ]));

        $this->package = $package;
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
