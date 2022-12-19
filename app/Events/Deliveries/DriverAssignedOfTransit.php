<?php

namespace App\Events\Deliveries;

use App\Broadcasting\User\PrivateChannel as UserPrivateChannel;
use App\Models\User;
use App\Models\Deliveries\Delivery;
use App\Models\Notifications\Template;
use App\Models\Partners\Transporter;
use Illuminate\Queue\SerializesModels;
use App\Models\Partners\Pivot\UserablePivot;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class DriverAssignedOfTransit
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var \App\Models\Deliveries\Delivery
     */
    public Delivery $delivery;

    /**
     * @var \App\Models\Partners\Transporter
     */
    public Transporter $transporter;

    public User $user;

    /** @var Template $notification */
    public Template $notification;

    /**
     * Create a new event instance.
     *
     * @param \App\Models\Deliveries\Delivery $delivery
     * @param \App\Models\Partners\Pivot\UserablePivot $userablePivot
     */
    public function __construct(Delivery $delivery, UserablePivot $userablePivot)
    {
        $this->delivery = $delivery;
        $this->transporter = $userablePivot->userable;
        $this->user = $userablePivot->user;

        $this->notification = Template::where('type', Template::TYPE_DRIVER_GET_ALERT_ONE_LEVEL)->first();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return UserPrivateChannel
     */
    public function broadcastOn()
    {
        // return new PrivateChannel('channel-name');
        return new UserPrivateChannel($this->user, $this->notification, ['package_code' => $this->delivery->code->content]);
    }
}
