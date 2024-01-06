<?php

namespace App\Events\Deliveries\Transit;

use App\Broadcasting\User\PrivateChannel as UserPrivateChannel;
use App\Models\Deliveries\Delivery;
use App\Models\Notifications\Template;
use App\Models\Partners\Pivot\UserablePivot;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Support\Collection;

class DriverUnloadedPackageInDestinationWarehouse
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var \App\Models\Deliveries\Delivery
     */
    public Delivery $delivery;

    /**
     * @var Collection
     */
    public Collection $user;

    /**
     * @var Template
     */
    public Template $notification;

    /**
     * Create a new event instance.
     *
     * @param \App\Models\Deliveries\Delivery $delivery
     */
    public function __construct(Delivery $delivery)
    {
        $this->delivery = $delivery;

        if (! is_null($delivery->partner) && $delivery->partner) {
            $this->user = $delivery->partner->users()->wherePivotIn('role', [UserablePivot::ROLE_WAREHOUSE])->get();
        } else {
            // Set an empty collection if $delivery->partner is null
            $this->user = collect();
        }

        $this->notification = Template::where('type', Template::TYPE_WAREHOUSE_GOOD_RECEIVE)->first();
    }

    /**
     * Broadcast to warehouse.
     */
    public function broadcast(): void
    {
        $notif = $this->notification;
        $delivery = $this->delivery;

        $this->user->each(function ($q) use ($notif, $delivery) {
            new UserPrivateChannel($q, $notif, ['package_code' => $delivery->code->content]);
        });
    }
}
