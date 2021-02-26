<?php

namespace App\Events\Users;

use App\Models\Partners\Pivot\UserablePivot;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserRoleModified
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public UserablePivot $userable;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(UserablePivot $userable)
    {
        $this->userable = $userable;
    }
}
