<?php

namespace App\Http\Controllers\Api\Auth;

use Illuminate\Http\Request;
use App\Models\OneTimePassword;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Actions\Auth\AccountAuthentication;

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
            'username' => ['required'],
            'password' => ['required'],
            'otp' => ['nullable', 'boolean'],
            'otp_channel' => [Rule::requiredIf(fn () => $request->otp), Rule::in(OneTimePassword::OTP_CHANNEL)],
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
            'otp_channel' => ['required', Rule::in(OneTimePassword::OTP_CHANNEL)],
        ]);

        return (new AccountAuthentication($request->all()))->register();
    }
}
