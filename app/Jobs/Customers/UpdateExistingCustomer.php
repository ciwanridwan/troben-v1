<?php

namespace App\Jobs\Customers;

use Illuminate\Bus\Batchable;
use App\Models\Customers\Customer;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Bus\Dispatchable;

class UpdateExistingCustomer
{
    use Dispatchable, SerializesModels, InteractsWithQueue, Batchable;

    /**
     * Customer instance.
     *
     * @var \App\Models\Customers\Customer
     */
    public Customer $customer;

    /**
     * Filtered attributes.
     *
     * @var array
     */
    public array $attributes;

    /**
     * UpdateExistingCustomer constructor.
     *
     * @param \App\Models\Customers\Customer $customer
     * @param array                          $inputs
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function __construct(Customer $customer, $inputs = [])
    {
        $this->attributes = Validator::make($inputs, [
            // TODO: add validation rules.
        ])->validate();
    }

    /**
     * Handle the job.
     *
     * @return bool
     */
    public function handle(): bool
    {
        // TODO:: update existing customer logic.

        // TODO:: fire success event.
        // TODO:: fire failed event.
    }
}
