<?php

namespace App\Actions\Auth;

use App\Concerns\RestfulResponse;
use Illuminate\Http\JsonResponse;
use App\Jobs\Customers\CreateNewCustomer;
use Illuminate\Foundation\Bus\DispatchesJobs;

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
        $this->attributes['guard'] = $this->attributes['guard'] ?? 'customer';

        switch ($this->attributes['guard']) {
            case 'customer':
                $job = new CreateNewCustomer($this->attributes);
                break;
        }

        $this->dispatch($job);
        $otp = $job->customer->createOtp();

        return $this->success([
            'otp_token' => $otp->token,
        ]);
    }
}
