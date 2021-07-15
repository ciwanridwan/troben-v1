<?php

namespace App\Jobs\Partners;

use Illuminate\Bus\Batchable;
use Illuminate\Validation\Rule;
use App\Models\Partners\Partner;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Validator;
use App\Events\Partners\NewPartnerCreated;
use Illuminate\Foundation\Bus\Dispatchable;

class CreateNewPartner
{
    use Dispatchable, InteractsWithQueue, Batchable, SerializesModels;

    /**
     * Partner Instance.
     *
     * @var \App\Models\Partners\Partner
     */
    public Partner $partner;

    /**
     * filtered attributes.
     *
     * @var array
     */
    protected array $attributes;

    /**
     * CreateNewPartner constructor.
     *
     * @param array $inputs
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function __construct($inputs = [])
    {
        $this->partner = new Partner();
        $this->attributes = Validator::make($inputs, [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email'],
            'contact_phone' => ['nullable', 'numeric', 'phone:AUTO,ID'],
            'geo_province_id' => ['nullable', 'exists:geo_provinces,id'],
            'geo_regency_id' => ['nullable', 'exists:geo_regencies,id'],
            'geo_district_id' => ['nullable', 'exists:geo_districts,id'],
            'geo_sub_district_id' => ['nullable', 'exists:geo_sub_districts,id'],
            'address' => ['nullable', 'string', 'max:255'],
            'geo_location' => ['nullable'],
            'type' => ['required', Rule::in(Partner::getAvailableTypes())],
        ])->validate();
    }

    /**
     * Execute the job.
     *
     * @return bool
     */
    public function handle(): bool
    {
        $this->partner->fill($this->attributes);

        if ($this->partner->save()) {
            event(new NewPartnerCreated($this->partner));
        }

        return $this->partner->exists;
    }
}
