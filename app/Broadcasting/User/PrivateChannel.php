<?php

namespace App\Broadcasting\User;

use App\Abstracts\TrawlNotification;
use App\Models\Notifications\Notification;
use App\Models\Notifications\Template;
use App\Models\User;

class PrivateChannel extends TrawlNotification
{
    /**
     * User's private channel constructs.
     *
     * @param User $user
     * @param Template $notification
     * @param array $data
     */
    public function __construct(User $user, Template $notification, array $data = [])
    {
        $this->user = $user;
        $this->notification = $notification;
        $this->data = $data;
        $this->validateData()
            ->recordLog();

        if ($this->user->fcm_token) $this->push();
    }

    /**
     * Store notification to notifiables table on database.
     *
     * @return $this
     */
    public function recordLog(): self
    {
        $this->user->notifications()->save((new Notification())->setAttribute('data', $this->template));
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
