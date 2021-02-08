<?php

namespace App\Actions\Auth;

use App\Models\User;
use App\Concerns\RestfulResponse;
use Illuminate\Http\JsonResponse;
use App\Models\Customers\Customer;
use App\Jobs\Customers\CreateNewCustomer;
use Illuminate\Foundation\Bus\DispatchesJobs;

class AccountRegister
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

    /**
     * Account Register.
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function register(): JsonResponse
    {
        $this->attributes['guard'] = $this->attributes['guard'] ?? 'customer';
        $account = ($this->attributes['guard'] === 'customer')
            ? $this->customerRegistration()
            : $this->userRegistration();

        $otp = $account->createOtp();

        return $this->success([
            'otp' => $otp->getKey(),
        ]);
    }

    /**
     * Customer registration.
     *
     * @return \App\Models\Customers\Customer
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function customerRegistration(): Customer
    {
        $job = new CreateNewCustomer($this->attributes);
        $this->dispatch($job);

        return $job->customer;
    }

    protected function userRegistration(): User
    {
        // TODO: add user registration
    }
}
