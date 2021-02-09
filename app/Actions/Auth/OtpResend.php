<?php

namespace app\Actions\Auth;

use App\Exceptions\Error;
use App\Http\Response;
use App\Models\OneTimePassword;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class OtpResend
{

    /**
     * @var array
     */
    public array $attributes;

    function __construct($inputs = [])
    {
        $this->attributes = $inputs;
    }

    /**
     * @return JsonResponse
     */
    public function resend(): JsonResponse
    {
        $otp = OneTimePassword::find($this->attributes['otp']);

        throw_if(!($otp->verifiable), new Error(Response::RC_MISMATCH_TOKEN_OWNERSHIP));

        $otp = $this->attributes['retry'] ? $this->extendExpired($otp) : $otp->verifiable->createOtp();

        return (new Response(Response::RC_SUCCESS, [
            'otp' => $otp->getKey()
        ]))->json();
    }

    /**
     * @param OneTimePassword $otp
     *
     * @return OneTimePassword
     */
    function extendExpired(OneTimePassword $otp): OneTimePassword
    {
        if (Carbon::now()->gt($otp->expired_at)) {
            $otp->expired_at = Carbon::now()->addMinutes(3);
            $otp->save();
        }
        return $otp;
    }
}
