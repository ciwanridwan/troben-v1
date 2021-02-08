<?php

namespace App\Actions\Auth;

use App\Models\OneTimePassword;
use App\Concerns\RestfulResponse;
use Illuminate\Http\JsonResponse;
use App\Jobs\OneTimePasswords\VerifyOtpToken;
use Illuminate\Foundation\Bus\DispatchesJobs;

class OtpVerify
{
    use DispatchesJobs, RestfulResponse;

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

        return $this->success([
            'access_token' => $account->createToken($this->attributes['device_name'])->plainTextToken,
        ]);
    }
}
