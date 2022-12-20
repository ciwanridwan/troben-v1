<?php

namespace App\Events\Payment\Nicepay;

use App\Broadcasting\User\PrivateChannel as UserPrivateChannel;
use App\Models\Notifications\Template;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Packages\Package;
use App\Models\Partners\Pivot\UserablePivot;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class PayByNicePayDummy
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var Package $package
     */
    public Package $package;

    /** @var Template $notification */
    public Template $notification;

    /**
     * @var User $user
     */
    public Collection $user;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Package $package)
    {
        $this->package = $package;

        $this->notification = Template::where('type', Template::TYPE_WAREHOUSE_START_PACKING)->first();
// dd('sa');
        $this->user = $this->package->deliveries->first()->partner->users()->wherePivotIn('role', [UserablePivot::ROLE_WAREHOUSE, UserablePivot::ROLE_OWNER])->get();
    }

    /**
     * Broadcast To Owner And Warehouse
     */
    public function broadcast(): void
    {
        $package = $this->package;
        $notification = $this->notification;

        $this->user->each(function ($q) use ($package, $notification) {
            new UserPrivateChannel($q, $notification, ['package_code' => $package->code->content]);
        });
    }
}
