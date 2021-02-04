<?php

namespace App\Actions\Auth;

use App\Concerns\RestfulResponse;
use App\Jobs\Customers\CreateNewCustomer;
use App\Models\Customers\Customer;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\JsonResponse;

class CustomerRegister
{
    use RestfulResponse, DispatchesJobs;

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

    public function register(): JsonResponse
    {

        switch ($this->attributes['guard']) {
            case 'customer':
                $this->dispatch(new CreateNewCustomer($this->attributes));
                $query = Customer::query();
                break;
                // case 'user':
                //     $query = User::query();
                //     // user Register
                //     break;
        }

        $authenticatable = $query->where('email', $this->attributes['email'])->first();

        $otp = $authenticatable->createOtp();

        return $this->success([
            'otp_id' => $otp->id,
            'expired_at' => $otp->expired_at->timestamp,
        ]);
    }
}
