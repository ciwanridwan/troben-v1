<?php

namespace App\Events\Packages;

use App\Models\User;
use App\Models\Packages\Package;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Validation\ValidationException;
use Illuminate\Broadcasting\InteractsWithSockets;

class WarehouseIsStartPacking
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var \App\Models\Packages\Package
     */
    public Package $package;
    public User $actor;

    /**
     * Create a new event instance.
     *
     * @return void
     * @throws \Throwable
     */
    public function __construct(Package $package)
    {
        throw_if(
            $package->status !== Package::STATUS_WAITING_FOR_PACKING || $package->payment_status !== Package::PAYMENT_STATUS_PAID,
            ValidationException::withMessages([
                'package' => __('package not ready to be packed.'),
            ]));

        $this->package = $package;

        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
        $this->actor = auth()->user();
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
