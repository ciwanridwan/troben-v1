<?php

namespace App\Jobs\Users\Actions;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Bus\Dispatchable;

class VerifyExistingUser
{
    use Dispatchable;

    public User $user;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->user->setAttribute('verified_at', Carbon::now())->save();
        return $this->user->verified_at != null;
    }
}
