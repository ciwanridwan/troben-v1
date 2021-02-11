<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Customers\Customer;
use App\Http\Controllers\Controller;
use App\Jobs\Users\UpdateExistingUser;
use App\Http\Resources\Account\UserResource;
use App\Jobs\Customers\UpdateExistingCustomer;
use App\Http\Resources\Account\CustomerResource;
use App\Http\Requests\Api\Account\UpdateAccountRequest;

class AccountController extends Controller
{
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
     * Update Account Information
     * Route Path       : {API_DOMAIN}/me
     * Route Name       : api.me.update
     * Route Method     : POST/PUT.
     *
     * @param \App\Http\Requests\Api\Account\UpdateAccountRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(UpdateAccountRequest $request): JsonResponse
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
        return $this->jsonSuccess(new CustomerResource($account));
    }
    /**
     * @param User $account
     *
     * @return JsonResponse
     */
    public function getUserInfo(User $account): JsonResponse
    {
        return $this->jsonSuccess(new UserResource($account));
    }

    /**
     * @param \App\Models\Customers\Customer                        $customer
     * @param \App\Http\Requests\Api\Account\UpdateAccountRequest   $inputs
     *
     * @return \App\Models\Customers\Customer
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function updateCustomer(Customer $customer, UpdateAccountRequest $inputs): Customer
    {
        $job = new UpdateExistingCustomer($customer, $inputs->all());
        $this->dispatch($job);

        return $job->customer->fresh();
    }

    /**
     * @param \App\Models\User                                      $user
     * @param \App\Http\Requests\Api\Account\UpdateAccountRequest   $inputs
     *
     * @return \App\Models\User
     */
    protected function updateUser(User $user, UpdateAccountRequest $inputs): User
    {
        $job = new UpdateExistingUser($user, $inputs->all());
        $this->dispatch($job);

        return $job->user->fresh();
    }
}
