<?php

namespace App\Events\Users;

use App\Models\User;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class NewUserCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * User instance.
     *
     * @var \App\Models\User
     */
    public User $user;

    /**
     * Event Create new User.
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }
}
