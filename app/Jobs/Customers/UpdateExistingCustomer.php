<?php

namespace App\Jobs\Customers;

use App\Events\Customers\CustomerModificationFailed;
use App\Events\Customers\CustomerModified;
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
        $this->customer = $customer;
        $this->attributes = Validator::make($inputs, [
            // TODO: add validation rules.
            'name' => ['filled'],
            'email' => ['filled', 'email', 'unique:customers,email,' . $customer->id . ',id,deleted_at,NULL'],
            'phone' => ['filled', 'numeric', 'phone:AUTO,ID'],
            'password' => ['filled', 'min:8', 'alpha_num'],
            'fcm_token' => ['nullable'],
            'facebook_id' => ['nullable'],
            'google_id' => ['nullable'],
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
        foreach ($this->attributes as $key => $value) {
            $this->customer->$key = $value;
        }

        if ($this->customer->isDirty()) {
            if ($this->customer->save()) {
                // TODO:: fire success event.
                event(new CustomerModified);
            } else {
                // TODO:: fire failed event.
                event(new CustomerModificationFailed);
            }
        } else {
        }

        return $this->customer->exists;
    }
}
