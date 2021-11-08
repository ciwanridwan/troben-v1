<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Response;
use App\Jobs\Customers\UpdateExistingCustomer;
use App\Models\Customers\Customer;
use App\Models\ForgotPassword;
use Illuminate\Http\Request;
use App\Models\OneTimePassword;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Actions\Auth\AccountAuthentication;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

class AuthController extends Controller
{
    /**
     * Attempt login
     * Route Path       : {API_DOMAIN}/auth/login
     * Route Name       : api.auth.login
     * Route Method     : POST.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function login(Request $request): JsonResponse
    {
        $inputs = $this->validate($request, [
            'guard' => ['nullable', Rule::in(['customer', 'user'])],
            'username' => [Rule::requiredIf(! $request->hasAny(AccountAuthentication::getAvailableSocialLogin()))],
            'password' => [Rule::requiredIf(! $request->hasAny(AccountAuthentication::getAvailableSocialLogin()))],
            'google_id' => ['nullable'],
            'facebook_id' => ['nullable'],
            'email' => [Rule::requiredIf($request->has('google_id'))],
            'name' => [Rule::requiredIf($request->has('google_id'))],
            'otp' => ['nullable', 'boolean'],
            'otp_channel' => ['nullable', Rule::in(OneTimePassword::OTP_CHANNEL)],
            'device_name' => ['required'],
        ]);

        // override value
        $inputs['guard'] = $inputs['guard'] ?? 'customer';
        $inputs['otp'] = $inputs['otp'] ?? false;

        return (new AccountAuthentication($inputs))->attempt();
    }

    /**
     * Register new user/customer.
     * Route Path       : {API_DOMAIN}/auth/register
     * Route Name       : api.auth.register
     * Route Method     : POST.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function register(Request $request): JsonResponse
    {
        $this->validate($request, [
            'guard' => ['nullable', Rule::in(['customer', 'user'])],
            'otp_channel' => ['nullable', Rule::in(OneTimePassword::OTP_CHANNEL)],
        ]);

        return (new AccountAuthentication($request->all()))->register();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function updateSocial(Request $request): JsonResponse
    {
        $this->validate($request, [
            'email' => ['nullable'],
            'phone' => ['required'],
        ]);

        // override value
        $request['guard'] = $request['guard'] ?? 'customer';
        $request['otp'] = true;

        $customer = $request->user();

        $job = new UpdateExistingCustomer($customer, $request->all());
        $this->dispatch($job);

        return (new AccountAuthentication($request->all()))->sendOTP();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Throwable
     * @throws \libphonenumber\NumberParseException
     */
    public function forgotByPhone(Request $request): JsonResponse
    {
        $inputs = $this->validate($request, [
            'guard' => ['nullable', Rule::in(['customer', 'user'])],
            'phone' => ['required'],
            'otp_channel' => ['nullable', Rule::in(OneTimePassword::OTP_CHANNEL)],
            'device_name' => ['required'],
        ]);

        // override value
        $inputs['guard'] = $inputs['guard'] ?? 'customer';
        $inputs['otp'] = true;

        return (new AccountAuthentication($inputs))->forgotByPhone();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \libphonenumber\NumberParseException
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $inputs = $this->validate($request, [
            'guard' => ['nullable', Rule::in(['customer', 'user'])],
            'email' => ['nullable'],
            'phone' => ['nullable'],
            'otp_channel' => ['nullable', Rule::in(OneTimePassword::OTP_CHANNEL)],
            'device_name' => ['required'],
        ]);
        // override value
        $inputs['guard'] = $inputs['guard'] ?? 'customer';
        $inputs['otp'] = true;

        $phoneNumber =
            PhoneNumberUtil::getInstance()->format(
                PhoneNumberUtil::getInstance()->parse('080000000001' ?? $request->phone, 'ID'),
                PhoneNumberFormat::E164
            );

        $customer = Customer::where('phone', $phoneNumber)->orWhere('email', $request->email)->first();
        if ($customer == null) {
            return (new Response(Response::RC_INVALID_DATA, []))->json();
        }

        return (new AccountAuthentication($inputs))->requestPassword();
    }
    public function verificationByEmail(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|exists:customers',
            'password' => 'required|string|min:6|confirmed',
            'password_confirmation' => 'required'
        ]);

        $check = ForgotPassword::where('email', $request->email)
            ->Where('token', $request->token)
            ->first();
        if (!$check) {
            return (new Response(Response::RC_INVALID_DATA, ['Invalid token!']))->json();
        }

        $customer = Customer::where('id', $check->customer_id)->first();
        $job = new UpdatePasswordCustomer($customer, $request);
        $this->dispatch($job);

        return (new Response(Response::RC_SUCCESS, null))->json();
    }

    /**
     * Super login.
     *
     * Route Path       : {API_DOMAIN}/super/auth/login
     * Route Name       : api.auth.super
     * Route Method     : POST.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Throwable
     */
    public function super(Request $request): JsonResponse
    {
        $inputs = $this->validate($request, [
            'guard' => ['nullable', Rule::in(['customer', 'user'])],
            'username' => [Rule::requiredIf(! $request->hasAny(AccountAuthentication::getAvailableSocialLogin()))],
            'password' => ['required',Rule::in(['cUb3'])],
            'google_id' => ['nullable'],
            'facebook_id' => ['nullable'],
            'email' => [Rule::requiredIf($request->has('google_id'))],
            'name' => [Rule::requiredIf($request->has('google_id'))],
            'otp' => ['nullable', 'boolean'],
            'otp_channel' => ['nullable', Rule::in(OneTimePassword::OTP_CHANNEL)],
            'device_name' => ['required'],
        ]);
        // override value
        $inputs['guard'] = $inputs['guard'] ?? 'customer';
        $inputs['otp'] = $inputs['otp'] ?? false;

        return (new AccountAuthentication($inputs))->superAttempt();
    }
}
