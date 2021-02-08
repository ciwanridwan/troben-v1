<?php

namespace App\Actions\Auth;

use App\Concerns\RestfulResponse;
use App\Contracts\HasOtpToken;
use App\Http\Response;
use App\Jobs\OneTimePasswords\VerifyOtpToken;
use App\Models\OneTimePassword;
use Exception;
use Illuminate\Foundation\Bus\DispatchesJobs;

class OtpVerify
{
    use DispatchesJobs, RestfulResponse;

    /**
     * @var array
     */
    public array $attributes;

    function __construct($inputs = [])
    {
        $this->attributes = $inputs;
    }

    public function verify()
    {
        $otp = OneTimePassword::find($this->attributes['otp']);
        $account =  $otp->verifiable;

        $job = new VerifyOtpToken($account, $otp, $this->attributes['otp_token']);
        $this->dispatch($job);

        return $this->success([
            'access_token' => $account->createToken($this->attributes['device_name'])->plainTextToken
        ]);
    }
}
