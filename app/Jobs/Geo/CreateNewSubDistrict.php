<?php

namespace App\Jobs\Geo;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use App\Models\Geo\SubDistrict;
use Illuminate\Queue\SerializesModels;
use App\Events\Geo\SubDistrictModified;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class CreateNewSubDistrict implements ShouldQueue
{
    use Dispatchable, SerializesModels, Queueable, InteractsWithQueue, Batchable;

    /**
     * Filtered attributes.
     *
     * @var array
     */
    protected array $attributes;

    /**
     * CreateNewSubDistrict constructor.
     *
     * @param array $inputs
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function __construct(array $inputs = [])
    {
        $this->attributes = Validator::make($inputs, [
            'country_id' => 'required|exists:geo_countries,id',
            'province_id' => 'required|exists:geo_provinces,id',
            'regency_id' => 'required|exists:geo_regencies,id',
            'district_id' => 'required|exists:geo_districts,id',
            'name' => 'required',
            'zip_code' => 'required',
        ])->validate();
    }

    /**
     * Handle the job.
     *
     * @return bool
     */
    public function handle(): bool
    {
        $subDistrict = new SubDistrict();
        $subDistrict->fill($this->attributes);

        if ($subDistrict->save()) {
            event(new SubDistrictModified($subDistrict, true));
        }

        return $subDistrict->exists;
    }
}
