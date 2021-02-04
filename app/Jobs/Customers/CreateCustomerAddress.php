<?php

namespace App\Jobs\Customers;

use App\Models\Customers\Address;
use App\Models\Customers\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Validator;

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
            'name' => ['required'],
            'address' => ['required'],
            'geo_location' => ['nullable'],
            'geo_province_id' => ['required', 'exists:geo_provinces,id'],
            'geo_regency_id' => ['required', 'exists:geo_regencies,id'],
            'geo_district_id' => ['required', 'exists:geo_districts,id'],
            'is_default' => ['nullable', 'boolean']
        ])->validate();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $customer_address = new Address($this->attributes);
        if ($this->customer->addresses()->save($customer_address)) {
            // event fire on address create
        }

        return $this->customer->addresses->find($customer_address)->exists;
    }
}
