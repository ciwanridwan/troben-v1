<?php

namespace App\Events\Deliveries;

use App\Broadcasting\User\PrivateChannel as UserPrivateChannel;
use App\Models\Deliveries\Delivery;
use App\Models\Notifications\Template;
use App\Models\Partners\Pivot\UserablePivot;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class DeliveryCreatedWithDeadline
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Delivery $delivery;

    public Template $notification;

    public Collection $user;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Delivery $delivery)
    {
        $this->delivery = $delivery;

        $this->notification = Template::where('type', Template::TYPE_WAREHOUSE_REQUEST_TRANSPORTER)->first();

        $this->user = $this->delivery->origin_partner->users()->wherePivotIn('role', [UserablePivot::ROLE_WAREHOUSE])->get();
    }

    /**
     * Broadcast To Warehouse.
     */
    public function broadcast(): void
    {
        $delivery = $this->delivery;
        $notification = $this->notification;

        $this->user->each(function ($q) use ($notification, $delivery) {
            new UserPrivateChannel($q, $notification, ['package_code' => $delivery->code->content]);
        });
    }
}
