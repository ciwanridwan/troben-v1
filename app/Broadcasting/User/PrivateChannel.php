<?php

namespace App\Broadcasting\User;

use App\Models\User;

class PrivateChannel
{
    /**
     * Create a new channel instance.
     *
     * @return void
     */
    public function __construct(User $user, $title, $body)
    {
        fcm()
            ->toTopic($user->fcm_token) // $topic must an string (topic name)
            ->priority('normal')
            ->timeToLive(0)
            ->notification(['title' => $title, 'body' => $body])
            ->send();
    }
}
