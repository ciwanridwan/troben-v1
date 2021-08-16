<?php

namespace App\Jobs\Customers;

use Illuminate\Bus\Batchable;
use App\Models\Customers\Customer;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Validator;
use App\Events\Customers\CustomerModified;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Events\Customers\CustomerModificationFailed;

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
    public function __construct(Customer $customer, $request)
    {
        if (array_key_exists('phone', $request)) {
            $output = preg_replace('/^0/', '+62', $request['phone']);
            $replacements = ['phone' => $output];
            $request = array_replace($request, $replacements);
        }

        $this->attributes = Validator::make($request, [
            'name' => ['filled'],
            'email' => ['filled', 'email', 'unique:customers,email,'.$customer->id.',id,deleted_at,NULL'],
            'phone' => ['filled', 'numeric', 'phone:AUTO,ID', 'unique:customers,phone,'.$customer->id.',id,deleted_at,NULL'],
            'address' => ['filled'],
            'password' => ['filled', 'min:8', 'alpha_num'],
            'fcm_token' => ['nullable'],
            'facebook_id' => ['nullable'],
            'google_id' => ['nullable'],
        ])->validate();

        $this->customer = $customer;
    }

    /**
     * Handle the job.
     *
     * @return bool
     */
    public function handle(): bool
    {
        collect($this->attributes)->each(fn ($v, $k) => $this->customer->{$k} = $v);
        if ($this->customer->isDirty()) {
            if ($this->customer->save()) {
                event(new CustomerModified($this->customer));
            } else {
                event(new CustomerModificationFailed($this->customer));
            }
        }

        return $this->customer->exists;
    }
}
