<?php

namespace App\Jobs\Offices;

use App\Events\Offices\NewOfficerCreated;
use App\Models\Offices\Office;
use Illuminate\Bus\Batchable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Validator;

class CreateNewOfficer
{
    use Dispatchable, SerializesModels, InteractsWithQueue, Batchable;

    /**
     * @var Office
     */
    public Office $office;

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
        $this->office = new Office();

        if (array_key_exists('phone', $inputs)) {
            $output = preg_replace('/^0/', '+62', $inputs['phone']);
            $replacements = ['phone' => $output];
            $inputs = array_replace($inputs, $replacements);
        }

        $this->attributes = Validator::make($inputs, [
            'name' => ['required'],
            'username' => ['required'],
            'phone' => ['required', 'numeric', 'phone:AUTO,ID', 'unique:offices,phone,NULL,id,deleted_at,NULL'],
            'email' => ['required', 'email', 'unique:offices,email,NULL,id,deleted_at,NULL'],
            'password' => ['required', 'min:8', 'alpha_num'],
            'address' => ['required'],
            'is_active' => ['nullable', 'boolean'],
        ])->validate();
    }

    /**
     * Handle the job.
     *
     * @return bool
     */
    public function handle(): bool
    {
        $this->attributes['is_active'] = true;
        $this->office->fill($this->attributes);

        if ($this->office->save()) {
            event(new NewOfficerCreated($this->office));
        }

        return $this->office->exists;
    }
}
