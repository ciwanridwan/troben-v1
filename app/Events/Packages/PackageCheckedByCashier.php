<?php

namespace App\Events\Packages;

use App\Models\Packages\Package;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Validation\ValidationException;

class PackageCheckedByCashier
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Package $package;

    /**
     * Create a new event instance.
     *
     * @param \App\Models\Packages\Package $package
     */
    public function __construct(Package $package)
    {

        $mustConditions = [Package::STATUS_ESTIMATED, Package::STATUS_REVAMP];
        throw_if(!in_array($package->status, $mustConditions), ValidationException::withMessages([
            'package' => __('package should be in ' . implode(',', $mustConditions) . ' status'),
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
