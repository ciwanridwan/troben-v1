<?php

namespace App\Jobs\OneTimePasswords;

use App\Contracts\HasOtpToken;
use App\Events\OneTimePasswords\TokenVerified;
use App\Http\Response;
use Carbon\Carbon;
use App\Models\User;
use App\Models\OneTimePassword;
use App\Models\Customers\Customer;
use Illuminate\Foundation\Bus\Dispatchable;

class VerifyOtpToken
{
    use Dispatchable;

    /**
     * @var HasOtpToken $account
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
    public function __construct(HasOtpToken $account, OneTimePassword $otp, $token)
    {
        $this->otp = $otp;
        $this->account = $account;
        $this->token = $token;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        throw_if(($this->account->id !== $this->otp->verifiable_id) && !($this->account instanceof $this->otp->verifiable_type) && (Carbon::now() > $this->otp->expired_at), Response::RC_INVALID_AUTHENTICATION_HEADER);

        if ($this->otp->token === $this->token) {
            // set otp claimed
            $this->otp->claimed_at = Carbon::now();
            $this->otp->save();
            // set account verified
            $this->account->verified_at = Carbon::now();
            $this->account->save();
            event(new TokenVerified($this->account, $this->otp));
        }

        return $this->account->is_verified;
    }
}
