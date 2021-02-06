<?php

namespace App\Events\OneTimePasswords;

use App\Contracts\HasOtpToken;
use App\Models\OneTimePassword;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

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
