<?php

namespace App\Broadcasting\User;

use App\Abstracts\TrawlNotification;
use App\Models\Notifications\Notification;
use App\Models\User;

class PrivateChannel extends TrawlNotification
{
    /**
     * User's private channel constructs.
     *
     * @param User $user
     * @param Notification $notification
     * @param array $data
     */
    public function __construct(User $user, Notification $notification, array $data = [])
    {
        $this->user = $user;
        $this->notification = $notification;
        $this->data = $data;

        if ($this->user->fcm_token) $this
                ->recordLog()
                ->validateData()
                ->push();
    }

    /**
     * Store notification to notifiables table on database.
     *
     * @return $this
     */
    public function recordLog(): self
    {
        $this->notification->users()->attach($this->user->id);
        return $this;
    }

    /**
     * Push notification to user.
     */
    public function push(): void
    {
        fcm()
            ->toTopic($this->user->fcm_token)
            ->priority($this->notification->priority)
            ->timeToLive(0)
            ->notification($this->template)
            ->send();
    }
}
