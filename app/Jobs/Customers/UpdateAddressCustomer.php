<?php

namespace App\Jobs\Customers;

use App\Events\Customers\CustomerAddressCreated;
use App\Events\Customers\CustomerAddressModifiedFailed;
use App\Models\Customers\Address;
use App\Models\Customers\Customer;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateAddressCustomer implements ShouldQueue
{
    use Dispatchable, SerializesModels, InteractsWithQueue, Batchable;

    /**
     * Customer instance.
     *
     * @var \App\Models\Customers\Customer
     */
    public Customer $customer;
    public Address $address;

    /**
     * Filtered attributes.
     *
     * @var array
     */
    public array $attributes;

    /**
     * UpdateAddressCustomer constructor.
     * @param Address $address
     * @param array $request
     * @throws \Illuminate\Validation\ValidationException
     */
    public function __construct(Address $address)
    {
        $this->validate($address, [
            'customer_id' => ['required'],
            'name' => ['required'],
            'address' => ['required'],
            'geo_location' => ['nullable'],
            'geo_province_id' => ['required'],
            'geo_regency_id' => ['required'],
            'geo_district_id' => ['required'],
            'is_default' => ['nullable'],
        ]);

        $this->address = $address;
    }

    /**
     * Handle the job.
     *
     * @return bool
     */
    public function handle(): bool
    {
        collect($this->attributes)->each(fn ($v, $k) => $this->address->{$k} = $v);

        if ($this->customer->addresses()->isDirty()) {
            if ($this->customer->addresses()->updateOrCreate()) {
                event(new CustomerAddressCreated($this->address));
            } else {
                event(new CustomerAddressModifiedFailed($this->address));
            }
        }

        return $this->address->exists;
    }
}
