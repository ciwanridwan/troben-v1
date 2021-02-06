<?php

namespace App\Jobs\OneTimePasswords;

use Carbon\Carbon;
use App\Models\User;
use App\Models\OneTimePassword;
use App\Models\Customers\Customer;
use Illuminate\Foundation\Bus\Dispatchable;

class VerifyOtpToken
{
    use Dispatchable;

    /**
     * @var Customer|User $account
     */
    public $account;

    /**
     * @var string
     */
    public string $token;

    /**
     * @var OneTimePassword $otp
     */
    public OneTimePassword $otp;

    /**
     * Create a new job instance.
     *
     * @return void
     *
     * @param OneTimePassword $otp
     * @param mixed $token
     */
    public function __construct(OneTimePassword $otp, $token)
    {
        // temporary
        $this->otp = $otp;
        $this->account = (new $otp->verifiable_type)->find($otp->verifiable_id);
        $this->token = $token;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->otp->token === $this->token) {
            // set otp claimed
            $this->otp->claimed_at = Carbon::now();
            $this->otp->save();
            // set account verified
            $this->account->verified_at = Carbon::now();
            $this->account->save();
        }

        return $this->account->is_verified;
    }
}
