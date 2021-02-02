<?php

namespace App\Jobs\Auth;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use App\Models\Customers\Customer;
use Illuminate\Queue\SerializesModels;
use App\Events\Auth\NewCustomerCreated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class CreateNewCustomer implements ShouldQueue
{
    use Dispatchable, SerializesModels, Queueable, InteractsWithQueue, Batchable;

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
        }

        return $customer->exists;
    }
}
