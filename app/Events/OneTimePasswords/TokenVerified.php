<?php

namespace App\Events\OneTimePasswords;

use App\Contracts\HasOtpToken;
use App\Models\OneTimePassword;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class TokenVerified
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var OneTimePassword
     */
    public OneTimePassword $otp;

    /**
     * @var HasOtpToken
     */
    public HasOtpToken $account;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(HasOtpToken $account, OneTimePassword $otp)
    {
        $this->account = $account;
        $this->otp = $otp;
    }
}
