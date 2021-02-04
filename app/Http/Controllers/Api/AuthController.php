<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;
use App\Actions\Auth\AttemptLogin;
use App\Actions\Auth\CustomerRegister;
use App\Http\Controllers\Controller;


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
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request): JsonResponse
    {
        $inputs = $this->validate($request, [
            'guard' => ['nullable', Rule::in(['customer', 'user'])],
            'username' => 'required',
            'password' => 'required',
            'otp' => 'nullable|boolean',
            'device_name' => 'required',
        ]);

        // override value
        $inputs['guard'] = $inputs['guard'] ?? 'customer';
        $inputs['otp'] = $inputs['otp'] ?? false;

        return (new AttemptLogin($inputs))->attempt();
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
        $inputs = $this->validate($request, [
            'guard' => ['nullable', Rule::in(['customer', 'user'])],
        ]);

        $inputs['guard'] = $inputs['guard'] ?? 'customer';

        return (new CustomerRegister($request->all()))->register();
    }
}