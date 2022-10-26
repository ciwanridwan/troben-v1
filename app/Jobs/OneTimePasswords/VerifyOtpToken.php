<?php

namespace App\Jobs\OneTimePasswords;

use Carbon\Carbon;
use App\Http\Response;
use App\Exceptions\Error;
use App\Contracts\HasOtpToken;
use App\Models\OneTimePassword;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Events\OneTimePasswords\TokenVerified;
use App\Exceptions\OtpVerifyException;

class VerifyOtpToken
{
    use Dispatchable;

    /**
     * Account instance.
     *
     * @var \App\Models\Customers\Customer|\App\Models\User|HasOtpToken $account
     */
    public HasOtpToken $account;

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
     * @param \App\Contracts\HasOtpToken $account
     * @param OneTimePassword            $otp
     * @param mixed                      $token
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
     * @throws \Throwable
     */
    public function handle()
    {
        throw_if(! ($this->otp->claimed_at === null), new OtpVerifyException(Response::RC_TOKEN_WAS_CLAIMED));

        throw_if(! ($this->account->getkey() === (int) $this->otp->verifiable_id) || ! ($this->account instanceof $this->otp->verifiable_type), new OtpVerifyException(Response::RC_MISMATCH_TOKEN_OWNERSHIP));

        throw_if(! (Carbon::now()->lt($this->otp->expired_at)), new OtpVerifyException(Response::RC_TOKEN_HAS_EXPIRED));

        throw_if(! ($this->otp->token === $this->token), new OtpVerifyException(Response::RC_TOKEN_MISMATCH));

        // set otp claimed
        $this->otp->claimed_at = Carbon::now();
        $this->otp->save();
        // set account verified
        $this->account->{$this->account->getVerifiedColumn()} = Carbon::now();
        $this->account->save();
        event(new TokenVerified($this->account, $this->otp));


        return $this->account->is_verified;
    }
}
