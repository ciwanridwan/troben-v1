<?php

namespace App\Events\Users;

use Illuminate\Queue\SerializesModels;
use App\Models\Partners\Pivot\UserablePivot;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

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
