<?php

namespace App\Actions\Auth;

use App\Http\Response;
use App\Models\OneTimePassword;
use Illuminate\Http\JsonResponse;
use App\Jobs\OneTimePasswords\VerifyOtpToken;
use Illuminate\Foundation\Bus\DispatchesJobs;

class OtpVerify
{
    use DispatchesJobs;

    /**
     * @var array
     */
    public array $attributes;

    public function __construct($inputs = [])
    {
        $this->attributes = $inputs;
    }

    /**
     * Verify OTP token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function verify(): JsonResponse
    {
        $otp = OneTimePassword::find($this->attributes['otp']);
        $account = $otp->verifiable;

        $job = new VerifyOtpToken($account, $otp, $this->attributes['otp_token']);
        $this->dispatch($job);

        return (new Response(Response::RC_SUCCESS, [
            'access_token' => $account->createToken($this->attributes['device_name'])->plainTextToken,
        ]))->json();
    }
}
