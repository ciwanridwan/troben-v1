<?php

namespace App\Events\Deliveries;

use App\Broadcasting\User\PrivateChannel as UserPrivateChannel;
use App\Models\Deliveries\Delivery;
use App\Models\Notifications\Template;
use App\Models\Partners\Partner;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Class PartnerAssigned.
 */
class PartnerAssigned
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var \App\Models\Deliveries\Delivery
     */
    public Delivery $delivery;

    /**
     * @var \App\Models\Partners\Partner
     */
    public Partner $partner;

    /**
     * @var User $user
     */
    public User $user;

    /**
     * @var User $user
     */
    public Template $notification;

    /**
     * PartnerAssigned constructor.
     * @param Delivery $delivery
     * @param Partner $partner
     */
    public function __construct(Delivery $delivery, Partner $partner)
    {
        $this->delivery = $delivery;
        $this->partner = $partner;

        $this->user = $this->partner->owner()->first();
        $this->notification = Template::where('type', Template::TYPE_OWNER_SHOULD_TAKE_PACKAGE)->first();
    }

    /**
     * Broadcast to owner of MTAK.
     */
    public function broadcast(): void
    {
        new UserPrivateChannel($this->user, $this->notification, ['package_code' => $this->delivery->code->content]);
    }
}
