<?php

namespace App\Http\Controllers\Api\Customer;

use App\Concerns\RestfulResponse;
use App\Http\Controllers\Controller;
use App\Jobs\Customers\UpdateExistingCustomer;
use App\Models\Customers\Customer;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UpdateCustomerController extends Controller
{
    use RestfulResponse, DispatchesJobs;

    /**
     * Customer instance.
     * 
     * @var \App\Models\Customers\Customer
     */
    protected Customer $customer;

    /**
     * filtered attributes.
     * 
     * @var array
     */
    protected array $attributes = [];

    /**
     * Update Customer
     * Route Path       : {API_DOMAIN}/customer
     * Route Name       : api.customer.update
     * Route Method     : PUT.
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $this->attributes = Validator::make($request->all(), [
            'avatar' => ['nullable','file'],
            'name' => ['nullable'],
        ])->validate();

        $response = dispatch(new UpdateExistingCustomer($request->user(),$this->attributes));

        return response()->json($response);
    }
}
