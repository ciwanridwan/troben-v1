<?php

namespace App\Actions\Auth;

use App\Models\User;
use App\Http\Response;
use App\Exceptions\Error;
use App\Contracts\HasOtpToken;
use Illuminate\Http\JsonResponse;
use App\Models\Customers\Customer;
use libphonenumber\PhoneNumberUtil;
use Illuminate\Support\Facades\Hash;
use App\Jobs\Customers\CreateNewCustomer;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Validation\ValidationException;

class AccountAuthentication
{
    use DispatchesJobs;

    public const CREDENTIAL_EMAIL = 'email';
    public const CREDENTIAL_PHONE = 'phone';
    public const CREDENTIAL_USERNAME = 'username';

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
     * Account Register.
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function register(): JsonResponse
    {
        $this->attributes['guard'] = $this->attributes['guard'] ?? 'customer';
        $account = ($this->attributes['guard'] === 'customer')
            ? $this->customerRegistration()
            : $this->userRegistration();

        $otp = $account->createOtp();

        return (new Response(Response::RC_SUCCESS, [
            'otp' => $otp->getKey(),
        ]))->json();
    }

    /**
     * Attempt login.
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
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

        if (! $authenticatable || ! Hash::check($this->attributes['password'], $authenticatable->password)) {
            throw ValidationException::withMessages([
                'username' => ['The provided credentials are incorrect.'],
            ]);
        }

        // if not asking for otp, make sure that the user is verified before.
        throw_if(! $this->attributes['otp'] && ! $authenticatable->is_verified, Error::make(Response::RC_ACCOUNT_NOT_VERIFIED));

        return $this->attributes['otp']
            ? $this->askingOtpResponse($authenticatable)
            : (new Response(Response::RC_SUCCESS, [
                'access_token' => $authenticatable->createToken($this->attributes['device_name'])->plainTextToken,
            ]))->json();
    }

    /**
     * Customer registration.
     *
     * @return \App\Models\Customers\Customer
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function customerRegistration(): Customer
    {
        $job = new CreateNewCustomer($this->attributes);
        $this->dispatch($job);

        return $job->customer;
    }

    protected function userRegistration(): User
    {
        // TODO: add user registration
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

        return (new Response(Response::RC_SUCCESS, [
            'otp' => $otp->id,
            'expired_at' => $otp->expired_at->timestamp,
        ]))->json();
    }
}
