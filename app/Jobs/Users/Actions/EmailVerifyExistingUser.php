<?php

namespace App\Jobs\Users\Actions;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Bus\Dispatchable;

class EmailVerifyExistingUser
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
        $this->user->setAttribute('email_verified_at', Carbon::now())->save();
        return $this->user->email_verified_at != null;
    }
}
