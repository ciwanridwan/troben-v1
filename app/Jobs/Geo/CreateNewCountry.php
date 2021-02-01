<?php

namespace App\Jobs\Geo;

use App\Models\Geo\Country;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use App\Events\Geo\CountryModified;
use libphonenumber\PhoneNumberUtil;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class CreateNewCountry implements ShouldQueue
{
    use Dispatchable, SerializesModels, Queueable, InteractsWithQueue, Batchable;

    /**
     * Filtered attributes.
     *
     * @var array
     */
    protected array $attributes;

    /**
     * CreateNewCountry constructor.
     *
     * @param array $inputs
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function __construct($inputs = [])
    {
        $this->attributes = Validator::make($inputs, [
            'name' => 'required',
            'alpha2' => 'required|size:2',
            'alpha3' => 'required|size:3',
            'numeric' => 'nullable',
        ])->validate();
    }

    /**
     * Handle the job.
     *
     * @return bool
     */
    public function handle(): bool
    {
        $country = new Country();
        $country->fill($this->attributes);
        $country->phone_prefix = PhoneNumberUtil::getInstance()->getCountryCodeForRegion($this->attributes['alpha2']);

        if ($country->save()) {
            event(new CountryModified($country, true));
        }

        return $country->exists;
    }
}
