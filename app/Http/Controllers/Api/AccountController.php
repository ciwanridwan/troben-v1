<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Concerns\RestfulResponse;
use Illuminate\Http\JsonResponse;
use App\Models\Customers\Customer;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Jobs\Customers\UpdateExistingCustomer;

class AccountController extends Controller
{
    use RestfulResponse;
    /**
     * Get Account Information
     * Route Path       : {API_DOMAIN}/me
     * Route Name       : api.me
     * Route Method     : GET.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $account = $request->user();

        return $account instanceof Customer ? $this->getCustomerInfo($account) : $this->getUserInfo($account);
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request)
    {
        $account = ($request->user() instanceof Customer)
            ? $this->updateCustomer($request->user(), $request)
            : $this->updateUser($request->user(), $request);

        return $account instanceof Customer
            ? $this->getCustomerInfo($account)
            : $this->getUserInfo($account);
    }

    /**
     * @param Customer $account
     *
     * @return JsonResponse
     */
    public function getCustomerInfo(Customer $account): JsonResponse
    {
        return $this->success([
            'name' => $account->name,
            'email' => $account->email,
            'phone' => $account->phone,
        ]);
    }
    /**
     * @param User $account
     *
     * @return JsonResponse
     */
    public function getUserInfo(User $account): JsonResponse
    {
        return $this->success([
            'name' => $account->name,
            'email' => $account->email,
            'phone' => $account->phone,
        ]);
    }

    /**
     * @param \App\Models\Customers\Customer $customer
     * @param \Illuminate\Http\Request       $request
     *
     * @return \App\Models\Customers\Customer
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function updateCustomer(Customer $customer, Request $request): Customer
    {
        $inputs = Validator::make($request->all(), [
            'avatar' => ['nullable','file'],
            'name' => ['nullable'],
        ])->validate();

        $job = new UpdateExistingCustomer($request->user(), $inputs);

        $this->dispatch($job);

        return $job->customer;
    }

    protected function updateUser(User $user, Request $request): User
    {
        // TODO: update user.
    }
}
