<?php

namespace App\Jobs\Customers;

use Illuminate\Bus\Queueable;
use App\Models\Customers\Address;
use App\Models\Customers\Customer;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Events\Customers\CustomerAddressCreated;

class CreateCustomerAddress
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Customer instance.
     *
     * @var \App\Models\Customers\Customer
     */
    public Customer $customer;

    /**
     * Customer instance.
     *
     * @var \App\Models\Customers\Address
     */
    public Address $customer_address;

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
            'name' => ['required'],
            'address' => ['required'],
            'geo_location' => ['nullable'],
            'geo_province_id' => ['required', 'exists:geo_provinces,id'],
            'geo_regency_id' => ['required', 'exists:geo_regencies,id'],
            'geo_district_id' => ['required', 'exists:geo_districts,id'],
            'is_default' => ['nullable', 'boolean'],
        ])->validate();
    }

    /**
     * Execute the job.
     *
     * @return bool
     */
    public function handle(): bool
    {
        $this->customer_address = $this->customer->addresses()->create($this->attributes);

        if ($this->customer_address) {
            event(new CustomerAddressCreated($this->customer_address));
        }

        return $this->customer_address->exists;
    }
}
