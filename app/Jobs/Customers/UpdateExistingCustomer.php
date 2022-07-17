<?php

namespace App\Jobs\Customers;

use App\Models\User;
use Illuminate\Bus\Batchable;
use App\Models\Customers\Customer;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Validator;
use App\Events\Customers\CustomerModified;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Events\Customers\CustomerModificationFailed;
use App\Models\Partners\NotificationAgent;
use Illuminate\Support\Facades\DB;

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
     * @var string
     */
    public string $referral = 'success';
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
            'referral_code' => ['filled'],
            'email' => ['filled', 'email', 'unique:customers,email,'.$customer->id.',id,deleted_at,NULL'],
            'phone' => ['filled', 'numeric', 'phone:AUTO,ID', 'unique:customers,phone,'.$customer->id.',id,deleted_at,NULL'],
            'address' => ['filled'],
            'password' => ['filled', 'min:8'],
            'fcm_token' => ['nullable','unique:customers,fcm_token,'.$customer->id.',id,deleted_at,NULL'],
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
        $agent = null;
        if (array_key_exists('referral_code', $this->attributes)) {
            $rc = $this->attributes['referral_code'];
            if (User::where('referral_code', $rc)->first() == null) {
                // fallback, find from agent
                $agent = DB::table('agents')->where('referral_code', $rc)->first();
                if (is_null($agent)) {
                    return $this->referral = 'failed';
                }
            }
        }

        // customer is first time input referral_code and agent exist
        if (
            ($this->customer->referral_code == null || $this->customer->referral_code == '')
            && isset($this->attributes['referral_code'])
            && ! is_null($agent)) {
            NotificationAgent::create([
                'type' => 'referral_cust',
                'message' => 'telah join melalui referral code',
                'title' => $this->customer->name,
                'status' => 'sent',
                'agent_id' => $agent->id,
            ]);
        }

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
