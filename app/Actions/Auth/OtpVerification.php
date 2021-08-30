<?php

namespace App\Actions\Auth;

use Carbon\Carbon;
use App\Http\Response;
use App\Exceptions\Error;
use App\Jobs\OneTimePasswords\SmsMasking\SendMessage;
use App\Models\OneTimePassword;
use Illuminate\Http\JsonResponse;
use App\Jobs\OneTimePasswords\VerifyOtpToken;
use Illuminate\Foundation\Bus\DispatchesJobs;

class OtpVerification
{
    use DispatchesJobs;

    /**
     * @var array
     */
    public array $attributes;

    /**
     * OtpVerification constructor.
     *
     * @param array $inputs
     */
    public function __construct($inputs = [])
    {
        $this->attributes = $inputs;
    }

    /**
     * Verify otp token.
     *
     * @return JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function verify(): JsonResponse
    {
        $otp = OneTimePassword::find($this->attributes['otp']);
        $account = $otp->verifiable;

        $job = new VerifyOtpToken($account, $otp, $this->attributes['otp_token']);
        $this->dispatch($job);

        $account = AccountAuthentication::validationFcmToken($account);

        return (new Response(Response::RC_SUCCESS, [
            'access_token' => $account->createToken($this->attributes['device_name'])->plainTextToken,
            'fcm_token' => $account->fcm_token ?? null
        ]))->json();
    }

    /**
     * Resent otp verification.
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function resend(): JsonResponse
    {
        $otp = OneTimePassword::query()->findOrFail($this->attributes['otp']);

        throw_if(! ($otp->verifiable), new Error(Response::RC_MISMATCH_TOKEN_OWNERSHIP));

        $otp = $this->attributes['retry'] ? $this->extendExpired($otp) : $otp->verifiable->createOtp();

        $job = new SendMessage($otp, $otp->verifiable->phone);
        $this->dispatch($job);

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
            $otp->expired_at = Carbon::now()->addMinutes(OneTimePassword::TOKEN_TTL);
            $otp->save();
        }

        return $otp;
    }
}
