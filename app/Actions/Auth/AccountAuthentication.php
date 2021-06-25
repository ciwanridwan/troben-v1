<?php

namespace App\Actions\Auth;

use App\Jobs\Customers\Actions\CreateNewCustomerByFacebook;
use App\Jobs\Customers\Actions\CreateNewCustomerByGoogle;
use App\Models\User;
use App\Http\Response;
use App\Exceptions\Error;
use App\Contracts\HasOtpToken;
use Illuminate\Http\JsonResponse;
use App\Models\Customers\Customer;
use libphonenumber\PhoneNumberUtil;
use Illuminate\Support\Facades\Hash;
use App\Jobs\Customers\CreateNewCustomer;
use App\Jobs\OneTimePasswords\SmsMasking\SendMessage;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use libphonenumber\PhoneNumberFormat;

class AccountAuthentication
{
    use DispatchesJobs;

    public const CREDENTIAL_GOOGLE = 'google_id';
    public const CREDENTIAL_FACEBOOK = 'facebook_id';
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

    public static function getAvailableSocialLogin()
    {
        return [
            self::CREDENTIAL_FACEBOOK,
            self::CREDENTIAL_GOOGLE
        ];
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
        $this->attributes['otp_channel'] = $this->attributes['otp_channel'] ?? 'phone';
        $account = ($this->attributes['guard'] === 'customer')
            ? $this->customerRegistration()
            : $this->userRegistration();

        return $this->askingOtpResponse($account, $this->attributes['otp_channel']);
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
            case Arr::has($this->attributes, self::CREDENTIAL_GOOGLE):
                $this->attributes['username'] = $this->attributes[self::CREDENTIAL_GOOGLE];
                $column = self::CREDENTIAL_GOOGLE;
                break;
            case Arr::has($this->attributes, self::CREDENTIAL_FACEBOOK):
                $this->attributes['username'] = $this->attributes[self::CREDENTIAL_FACEBOOK];
                $column = self::CREDENTIAL_FACEBOOK;
                break;
            case filter_var($this->attributes['username'], FILTER_VALIDATE_EMAIL):
                $column = self::CREDENTIAL_EMAIL;
                break;
            case PhoneNumberUtil::getInstance()->isPossibleNumber($this->attributes['username'], 'ID'):
                $column = self::CREDENTIAL_PHONE;
                $this->attributes['username'] = PhoneNumberUtil::getInstance()->format(
                    PhoneNumberUtil::getInstance()->parse($this->attributes['username'], 'ID'),
                    PhoneNumberFormat::E164
                );
                break;
            default:
                $column = $this->attributes['guard'] == 'customer' ? self::CREDENTIAL_EMAIL : self::CREDENTIAL_USERNAME;
                break;
        }

        $this->attributes['otp_channel'] = $this->attributes['otp_channel'] ?? 'phone';

        $query = $this->attributes['guard'] === 'customer' ? Customer::query() : User::query();

        /** @var \App\Models\User|\App\Models\Customers\Customer|null $authenticatable */
        $authenticatable = $query->where($column, $this->attributes['username'])->first();

        if (in_array($column, self::getAvailableSocialLogin())) {
            if (!$authenticatable) {
                switch ($column) {
                    case self::CREDENTIAL_GOOGLE:
                        // TODO: store google account to database
                        $job = new CreateNewCustomerByGoogle($this->attributes);
                        $this->dispatch($job);
                        $authenticatable = $job->customer;
                        break;
                    case self::CREDENTIAL_FACEBOOK:
                        // TODO: store facebook account to database
                        $job = new CreateNewCustomerByFacebook($this->attributes);
                        $this->dispatch($job);
                        $authenticatable = $job->customer;

                        break;
                }
            }
            return (new Response(Response::RC_SUCCESS, [
                'access_token' => $authenticatable->createToken($this->attributes['device_name'])->plainTextToken,
            ]))->json();
            // TODO: get authenticatable
        }



        if (!$authenticatable || !Hash::check($this->attributes['password'], $authenticatable->password)) {
            throw ValidationException::withMessages([
                'username' => ['The provided credentials are incorrect.'],
            ]);
        }

        // if not asking for otp, make sure that the user is verified before.
        throw_if(!$this->attributes['otp'] && !$authenticatable->is_verified, Error::make(Response::RC_ACCOUNT_NOT_VERIFIED));

        return $this->attributes['otp']
            ? $this->askingOtpResponse($authenticatable, $this->attributes['otp_channel'])
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
    protected function askingOtpResponse(HasOtpToken $authenticatable, string $otp_channel): JsonResponse
    {
        $otp = $authenticatable->createOtp($otp_channel);

        $phone = PhoneNumberUtil::getInstance()->formatNumberForMobileDialing(
            PhoneNumberUtil::getInstance()->parse($authenticatable->phone, 'ID'),
            'ID',
            false
        );

        $job = new SendMessage($otp, $phone);
        $this->dispatch($job);

        return (new Response(Response::RC_SUCCESS, [
            'otp' => $otp->id,
            'expired_at' => $otp->expired_at->timestamp,
        ]))->json();
    }
}
