<?php

namespace App\Jobs\Customers;

use Illuminate\Bus\Batchable;
use App\Models\Customers\Customer;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Events\Customers\NewCustomerCreated;

class CreateNewCustomer
{
    use Dispatchable, SerializesModels, InteractsWithQueue, Batchable;

    /**
     * Customer Instance.
     *
     * @var \App\Models\Customers\Customer
     */
    public Customer $customer;

    /**
     * Filtered attributes.
     *
     * @var array
     */
    protected array $attributes;

    /**
     * CreateNewCustomer constructor.
     *
     * @param array $inputs
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function __construct($inputs = [])
    {
        $this->customer = new Customer();

        if (array_key_exists('phone', $inputs)) {
            $output = preg_replace('/^0/', '+62', $inputs['phone']);
            $replacements = ['phone' => $output];
            $inputs = array_replace($inputs, $replacements);
        }

        $this->attributes = Validator::make($inputs, [
            'name' => ['required'],
            'email' => ['required', 'email', 'unique:customers,email,NULL,id,deleted_at,NULL'],
            'phone' => ['required', 'numeric', 'phone:AUTO,ID', 'unique:customers,phone,NULL,id,deleted_at,NULL'],
            'password' => ['required', 'min:8'],
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
        $this->customer->fill($this->attributes);

        if ($this->customer->save()) {
            event(new NewCustomerCreated($this->customer));
        }

        return $this->customer->exists;
    }
}
