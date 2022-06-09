<?php

namespace App\Jobs\Users;

use App\Models\User;
use Illuminate\Bus\Batchable;
use App\Events\Users\UserDeleted;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class DeleteExistingUser
{
    use Dispatchable, InteractsWithQueue, SerializesModels, Batchable;

    /**
     * Service instance.
     *
     * @var \App\Models\User
     */
    public User $user;

    /**
     * Soft delete flag.
     *
     * @var bool
     */
    public bool $softDelete;

    /**
     * DeleteExistingUser constructor.
     *
     * @param \App\Models\User  $user
     * @param bool              $force
     *
     * @return void
     */
    public function __construct(User $user, $force = false)
    {
        $this->user = $user;
        $this->softDelete = ! $force;
    }

    /**
     * Execute DeleteExistingUser job.
     *
     * @return void
     */
    public function handle(): bool
    {
//        $this->user->phone = '';
//        $this->user->email = '';
//        $this->user->update();

        (bool) $result = $this->softDelete ? $this->user->delete() : $this->user->forceDelete();

        event(new UserDeleted($this->user));

        return $result;
    }
}
