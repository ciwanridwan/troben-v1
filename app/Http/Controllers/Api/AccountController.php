<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Concerns\RestfulResponse;
use Illuminate\Http\JsonResponse;
use App\Models\Customers\Customer;
use App\Http\Controllers\Controller;

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
    public function index(Request $request)
    {
        $account = $request->user();
        $response = $account instanceof Customer ? $this->getCustomerInfo($account) : $this->getUserInfo($account);

        return $response;
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
}
