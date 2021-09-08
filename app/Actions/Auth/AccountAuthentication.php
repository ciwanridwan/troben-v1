<?php

namespace App\Actions\Auth;

use App\Jobs\Customers\Actions\CreateNewCustomerByFacebook;
use App\Jobs\Customers\Actions\CreateNewCustomerByGoogle;
use App\Jobs\Customers\UpdateExistingCustomer;
use App\Jobs\Users\UpdateExistingUser;
use App\Models\User;
use App\Http\Response;
use App\Http\Resources\Account\JWTCustomerResource;
use App\Http\Resources\Account\JWTUserResource;
use App\Contracts\HasOtpToken;
use Illuminate\Http\JsonResponse;
use App\Models\Customers\Customer;
use Illuminate\Support\Str;
use libphonenumber\PhoneNumberUtil;
use Illuminate\Support\Facades\Hash;
use App\Jobs\Customers\CreateNewCustomer;
use App\Jobs\OneTimePasswords\SmsMasking\SendMessage;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use libphonenumber\PhoneNumberFormat;
use Firebase\JWT\JWT;

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

    public static function getAvailableSocialLogin(): array
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

        $key = 'trawlbensJWTSecretK';

        if (in_array($column, self::getAvailableSocialLogin())) {
            if (! $authenticatable) {
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

            # update fcm_token
            if ($authenticatable instanceof Customer || $authenticatable instanceof User) {
                $authenticatable = $this->validationFcmToken($authenticatable);
            }

            $customerPayload = new JWTCustomerResource($authenticatable);
            $customerPayload['id'] = $authenticatable->id;
            $jwtCustomer = JWT::encode($customerPayload, $key);

            if ($authenticatable->phone_verified_at == null) {
                return (new Response(Response::RC_ACCOUNT_NOT_VERIFIED, [
                    'message' => 'Harap lengkapi data anda!',
                    'access_token' => $authenticatable->createToken($this->attributes['device_name'])->plainTextToken,
                    'fcm_token' => $authenticatable->fcm_token ?? null,
                    'jwt_token' => $jwtCustomer
                ]))->json();
            }

            return (new Response(Response::RC_SUCCESS, [
                'access_token' => $authenticatable->createToken($this->attributes['device_name'])->plainTextToken,
                'fcm_token' => $authenticatable->fcm_token ?? null,
                'jwt_token' => $jwtCustomer
            ]))->json();
            // TODO: get authenticatable
        }

        # update fcm_token
        if ($authenticatable instanceof Customer || $authenticatable instanceof User) {
            $authenticatable = $this->validationFcmToken($authenticatable);
        }

        if (! $authenticatable || ! Hash::check($this->attributes['password'], $authenticatable->password)) {
            throw ValidationException::withMessages([
                'username' => ['The provided credentials are incorrect.'],
            ]);
        }
        if (! $this->attributes['otp']  && ! $authenticatable->is_verified) {
            return $this->attributes['otp']
                ?: $this->askingOtpResponseFailed($authenticatable, $this->attributes['otp_channel']);
        }

        if ($this->attributes['otp']) {
            return $this->askingOtpResponse($authenticatable, $this->attributes['otp_channel']);
        }

        // if ($this->attributes['guard'] === 'user') {
        //     $key = 'trawlbensJWTSecretK';

        //     $payload = [
        //         'id' => $authenticatable->id,
        //         'name' => $authenticatable->name,
        //         'email' => $authenticatable->email
        //     ];

        //     return (new Response(Response::RC_SUCCESS, [
        //         'access_token' => $authenticatable->createToken($this->attributes['device_name'])->plainTextToken,
        //         'fcm_token' => $authenticatable->fcm_token ?? null,
        //         'jwt_token' => JWT::encode($payload, $key)
        //     ]))->json();
        // }
        $payload = [];
        $payload = $this->attributes['guard'] === 'user' ? new JWTUserResource($authenticatable) : new JWTCustomerResource($authenticatable);
        $payload['id'] = $authenticatable->id;
        $jwt = JWT::encode($payload, $key);

        return (new Response(Response::RC_SUCCESS, [
            'access_token' => $authenticatable->createToken($this->attributes['device_name'])->plainTextToken,
            'fcm_token' => $authenticatable->fcm_token ?? null,
            'jwt_token' => $jwt
        ]))->json();
    }

    /**
     * @return JsonResponse
     * @throws ValidationException
     * @throws \Throwable
     * @throws \libphonenumber\NumberParseException
     */
    public function forgotByPhone(): JsonResponse
    {
        switch (true) {
            case PhoneNumberUtil::getInstance()->isPossibleNumber($this->attributes['phone'], 'ID'):
                $column = self::CREDENTIAL_PHONE;
                $this->attributes['phone'] = PhoneNumberUtil::getInstance()->format(
                    PhoneNumberUtil::getInstance()->parse($this->attributes['phone'], 'ID'),
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
        $authenticatable = $query->where($column, $this->attributes['phone'])->first();

        # update fcm_token
        if ($authenticatable instanceof Customer || $authenticatable instanceof User) {
            $authenticatable = $this->validationFcmToken($authenticatable);
        }

        return $this->attributes['otp']
            ? $this->askingOtpResponse($authenticatable, $this->attributes['otp_channel'])
            : (new Response(Response::RC_SUCCESS, [
                'access_token' => $authenticatable->createToken($this->attributes['device_name'])->plainTextToken,
                'fcm_token' => $authenticatable->fcm_token ?? null
            ]))->json();
    }


    public function sendOTP(): JsonResponse
    {
        switch (true) {
            case PhoneNumberUtil::getInstance()->isPossibleNumber($this->attributes['phone'], 'ID'):
                $column = self::CREDENTIAL_PHONE;
                $this->attributes['phone'] = PhoneNumberUtil::getInstance()->format(
                    PhoneNumberUtil::getInstance()->parse($this->attributes['phone'], 'ID'),
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
        $authenticatable = $query->where($column, $this->attributes['phone'])->first();

        # update fcm_token
        if ($authenticatable instanceof Customer || $authenticatable instanceof User) {
            $authenticatable = $this->validationFcmToken($authenticatable);
        }

        return $this->attributes['otp']
            ? $this->askingOtpResponse($authenticatable, $this->attributes['otp_channel'])
            : (new Response(Response::RC_SUCCESS, [
                'access_token' => $authenticatable->createToken($this->attributes['device_name'])->plainTextToken,
                'fcm_token' => $authenticatable->fcm_token ?? null,
            ]))->json();
    }

    /**
     * Validate fcm token.
     *
     * @param object|Customer|User $authenticatable
     * @return object
     * @throws ValidationException
     */
    public static function validationFcmToken(object $authenticatable): object
    {
        if (is_null($authenticatable->fcm_token)) {
            $input = ['fcm_token' => (string) Str::uuid()];
            if ($authenticatable instanceof Customer) {
                $job = new UpdateExistingCustomer($authenticatable, $input);
            } else {
                $input['fcm_token'] = 'usr-'.$input['fcm_token'];
                $job = new UpdateExistingUser($authenticatable, $input);
            }
            dispatch_now($job);
        }

        return $authenticatable->refresh();
    }

    /**
     * @param HasOtpToken $authenticatable
     * @param string $otp_channel
     * @return JsonResponse
     */
    protected function askingOtpResponseFailed(HasOtpToken $authenticatable, string $otp_channel): JsonResponse
    {
        $otp = $authenticatable->createOtp($otp_channel);
        $job = new SendMessage($otp, $authenticatable->phone);
        $this->dispatch($job);
        return (new Response(Response::RC_ACCOUNT_NOT_VERIFIED, [
            'message' => 'Harap cek kotak pesan SMS anda',
            'otp' => $otp->id,
            'expired_at' => $otp->expired_at->timestamp,
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
     * asking for otp response.
     *
     * @param HasOtpToken $authenticatable
     * @param string $otp_channel
     * @return JsonResponse
     */
    protected function askingOtpResponse(HasOtpToken $authenticatable, string $otp_channel): JsonResponse
    {
        $otp = $authenticatable->createOtp($otp_channel);
        $job = new SendMessage($otp, $authenticatable->phone);
        $this->dispatch($job);
        return (new Response(Response::RC_SUCCESS, [
            'otp' => $otp->id,
            'expired_at' => $otp->expired_at->timestamp,
        ]))->json();
    }
}
