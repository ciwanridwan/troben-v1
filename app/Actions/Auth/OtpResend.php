<?php

namespace app\Actions\Auth;

use Carbon\Carbon;
use App\Http\Response;
use App\Exceptions\Error;
use App\Models\OneTimePassword;
use Illuminate\Http\JsonResponse;

class OtpResend
{
    /**
     * @var array
     */
    public array $attributes;

    public function __construct($inputs = [])
    {
        $this->attributes = $inputs;
    }

    /**
     * @return JsonResponse
     */
    public function resend(): JsonResponse
    {
        $otp = OneTimePassword::find($this->attributes['otp']);

        throw_if(! ($otp->verifiable), new Error(Response::RC_MISMATCH_TOKEN_OWNERSHIP));

        $otp = $this->attributes['retry'] ? $this->extendExpired($otp) : $otp->verifiable->createOtp();

        return (new Response(Response::RC_SUCCESS, [
            'otp' => $otp->getKey(),
        ]))->json();
    }

    /**
     * @param OneTimePassword $otp
     *
     * @return OneTimePassword
     */
    public function extendExpired(OneTimePassword $otp): OneTimePassword
    {
        if (Carbon::now()->gt($otp->expired_at)) {
            $otp->expired_at = Carbon::now()->addMinutes(15);
            $otp->save();
        }

        return $otp;
    }
}
