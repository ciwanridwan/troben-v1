<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Jobs\Auth\CreateNewCustomer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{


    /**
     * register customer only
     * Route Path       : {API_DOMAIN}/auth/register
     * Route Method     : POST
     * Route Name       : api.auth.register.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): JsonResponse
    {
        $response = $this->storeCustomer($request->all());
        return response()->json($response);
    }


    public function storeCustomer($inputs = [])
    {
        $status =  $this->dispatch(new CreateNewCustomer($inputs));
        // only for data dummy
        if ($status === true) {
            $response = [
                'otp_token' => "0986"
            ];
        }
        return $response;
    }
}
