<?php

namespace App\Events\Users;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserModificationFailed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * User instance.
     * 
     * @var \App\Models\User
     */
    public User $user;

    /**
     * Event User Modification Failed
     * 
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }
}