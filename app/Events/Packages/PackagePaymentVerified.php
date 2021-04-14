<?php

namespace App\Events\Packages;

use App\Models\Packages\Package;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Validation\ValidationException;
use Illuminate\Broadcasting\InteractsWithSockets;

class PackagePaymentVerified
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var \App\Models\Packages\Package
     */
    public Package $package;

    /**
     * Create a new event instance.
     *
     * @return void
     * @throws \Throwable
     */
    public function __construct(Package $package)
    {

        throw_if(
            $package->payment_status !== Package::PAYMENT_STATUS_PENDING || $package->status !== Package::STATUS_ACCEPTED,
            ValidationException::withMessages([
                'package' => __('package not ready to be set to paid!'),
            ])
        );

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
