<?php

namespace App\Events\Packages;

use App\Models\Packages\Package;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Validation\ValidationException;
use Illuminate\Broadcasting\InteractsWithSockets;

class PackageApprovedByCustomer
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var \App\Models\Packages\Package
     */
    public Package $package;

    /**
     * Create a new event instance.
     *
     * @param \App\Models\Packages\Package $package
     * @throws \Throwable
     */
    public function __construct(Package $package)
    {
        throw_if($package->status !== Package::STATUS_WAITING_FOR_APPROVAL, ValidationException::withMessages([
            'package' => __('package should be in '.Package::STATUS_WAITING_FOR_APPROVAL.' status'),
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
