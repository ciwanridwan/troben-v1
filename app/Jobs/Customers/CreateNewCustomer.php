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
        $this->attributes = Validator::make($inputs, [
            'name' => ['required'],
            'email' => ['nullable', 'email', 'unique:customers,email,NULL,id,deleted_at,NULL'],
            'phone' => ['required', 'numeric', 'phone:AUTO,ID'],
            'password' => ['required', 'min:8', 'alpha_num'],
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
        $customer = new Customer();
        $customer->fill($this->attributes);

        if ($customer->save()) {
            // run event when customer created
            event(new NewCustomerCreated($customer));
            // send otp token
        }


        return $customer->exists;
    }
}