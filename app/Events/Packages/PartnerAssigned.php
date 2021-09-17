<?php

namespace App\Events\Packages;

use App\Broadcasting\User\PrivateChannel;
use App\Models\Notifications\Template;
use App\Models\Packages\Package;
use App\Models\Partners\Partner;
use App\Models\Partners\Pivot\UserablePivot;
use App\Models\User;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class PartnerAssigned
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Package $package;

    public Partner $partner;

    protected Template $notification;

    /**
     * Create a new event instance.
     *
     * @param \App\Models\Packages\Package $package
     * @param \App\Models\Partners\Partner $partner
     */
    public function __construct(Package $package, Partner $partner)
    {
        $this->package = $package;
        $this->partner = $partner;
        $this->setNotification();
    }

    /**
     * Broadcast to all.
     */
    public function broadcast(): void
    {
        $this->broadcastToCustomerService();
        $this->broadcastToAdmin();
    }

    /**
     * Broadcast to customer service.
     */
    public function broadcastToCustomerService(): void
    {
        $cs = $this->partner->users()->wherePivotIn('role', [UserablePivot::ROLE_CS,UserablePivot::ROLE_OWNER])->get();
        $package = $this->package;
        $notification = $this->notification;
        $cs->each(function ($cs) use ($notification, $package): void {
            new PrivateChannel(
                $cs,
                $notification,
                [
                    'package_code' => $package->code->content,
                ]
            );
        });
    }

    /**
     * Broadcast to admin.
     */
    public function broadcastToAdmin(): void
    {
        $admin = User::where('is_admin', true)->first();
        new PrivateChannel($admin, $this->notification, [
            'package_code' => $this->package->code->content,
        ]);
    }

    /**
     * Set notification property.
     */
    protected function setNotification(): void
    {
        $this->notification = Template::where('type', Template::TYPE_CS_GET_NEW_ORDER)->first();
    }
}
