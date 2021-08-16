<?php

namespace App\Jobs\Customers\Actions;

use App\Events\Customers\NewCustomerCreated;
use App\Models\Customers\Customer;
use Illuminate\Bus\Batchable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Validator;

class CreateNewCustomerByFacebook
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
        $this->attributes = Validator::make($inputs, [
            'name' => ['required'],
            'facebook_id' => ['required'],
        ])->validate();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): bool
    {
        $this->attributes['phone'] = '';
        $this->customer->fill($this->attributes);

        if ($this->customer->save()) {
            event(new NewCustomerCreated($this->customer));
        }

        return $this->customer->exists;
    }
}
