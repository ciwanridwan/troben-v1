<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Requests\Api\Account\UpdateAccountRequest;
use App\Jobs\Customers\CustomerUploadPhoto;
use App\Jobs\Customers\UpdateExistingCustomer;
use App\Models\Attachment;
use App\Models\Customers\Customer;
use Illuminate\Http\Request;
use App\Models\OneTimePassword;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Actions\Auth\AccountAuthentication;
use phpDocumentor\Reflection\Types\This;

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
            'email' => ['required'],
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
            'username' => ['required'],
            'otp_channel' => ['nullable', Rule::in(OneTimePassword::OTP_CHANNEL)],
            'device_name' => ['required'],
        ]);

        // override value
        $inputs['guard'] = $inputs['guard'] ?? 'customer';
        $inputs['otp'] = true;

        return (new AccountAuthentication($inputs))->forgotByPhone();
    }
}
