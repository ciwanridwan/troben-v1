<?php

namespace App\Actions\Auth;

use App\Models\User;
use App\Http\Response;
use App\Exceptions\Error;
use App\Contracts\HasOtpToken;
use App\Concerns\RestfulResponse;
use Illuminate\Http\JsonResponse;
use App\Models\Customers\Customer;
use libphonenumber\PhoneNumberUtil;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AttemptLogin
{
    use RestfulResponse;

    const CREDENTIAL_EMAIL = 'email';
    const CREDENTIAL_PHONE = 'phone';
    const CREDENTIAL_USERNAME = 'username';

    /**
     * Accepted attributes.
     *
     * @var array
     */
    public array $attributes;

    /**
     * AttemptLogin constructor.
     *
     * @param array $inputs
     */
    public function __construct(array $inputs)
    {
        $this->attributes = $inputs;
    }

    /**
     * Attempt login.
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     * @throws \libphonenumber\NumberParseException
     * @throws \Throwable
     */
    public function attempt(): JsonResponse
    {
        switch (true) {
            case filter_var($this->attributes['username'], FILTER_VALIDATE_EMAIL):
                $column = self::CREDENTIAL_EMAIL;
                break;
            case PhoneNumberUtil::getInstance()->isPossibleNumber($this->attributes['username']):
                $column = self::CREDENTIAL_PHONE;
                // $this->attributes['username'] = PhoneNumberUtil::getInstance()->format(
                //     PhoneNumberUtil::getInstance()->parse($this->attributes['username']),
                // PhoneNumberFormat::E164
                // );
                break;
            default:
                $column = self::CREDENTIAL_USERNAME;
                break;
        }

        $query = $this->attributes['guard'] === 'customer' ? Customer::query() : User::query();

        /** @var \App\Models\User|\App\Models\Customers\Customer|null $authenticatable */
        $authenticatable = $query->where($column, $this->attributes['username'])->first();

        if (!$authenticatable || !Hash::check($this->attributes['password'], $authenticatable->password)) {
            throw ValidationException::withMessages([
                'username' => ['The provided credentials are incorrect.'],
            ]);
        }

        // if not asking for otp, make sure that the user is verified before.
        throw_if(!$this->attributes['otp'] && !$authenticatable->is_verified, new Error(Response::RC_ACCOUNT_NOT_VERIFIED));

        return $this->attributes['otp']
            ? $this->askingOtpResponse($authenticatable)
            : $this->success([
                'access_token' => $authenticatable->createToken($this->attributes['device_name'])->plainTextToken,
            ]);
    }

    /**
     * Asking for OTP response.
     *
     * @param \App\Contracts\HasOtpToken $authenticatable
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function askingOtpResponse(HasOtpToken $authenticatable): JsonResponse
    {
        $otp = $authenticatable->createOtp();

        return $this->success([
            'otp_id' => $otp->id,
            'expired_at' => $otp->expired_at->timestamp,
        ]);
    }
}
