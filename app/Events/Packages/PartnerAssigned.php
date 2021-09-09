<?php

namespace App\Events\Packages;

use App\Broadcasting\User\PrivateChannel;
use App\Models\Notifications\Notification;
use App\Models\Packages\Package;
use App\Models\Partners\Partner;
use App\Models\Partners\Pivot\UserablePivot;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class PartnerAssigned
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Package $package;

    public Partner $partner;

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
    }

    /**
     * Broadcast to customer service
     */
    public function broadcastToCustomerService(): void
    {
        $cs = $this->partner->users()->wherePivotIn('role',[UserablePivot::ROLE_CS,UserablePivot::ROLE_OWNER])->get();
        $notification = Notification::where('type', Notification::TYPE_CS_GET_NEW_ORDER)->first();
        $package = $this->package;
        $cs->each(function ($cs) use ($notification, $package): void {
            new PrivateChannel($cs, $notification, [
                    'package_code' => $package->code->content,
                ]
            );
        });
    }
}
