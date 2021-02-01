<?php

namespace App\Jobs\Geo;

use App\Models\Geo\District;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use App\Events\Geo\DistrictModified;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class CreateNewDistrict implements ShouldQueue
{
    use Dispatchable, SerializesModels, Queueable, InteractsWithQueue, Batchable;

    /**
     * Filtered attributes.
     *
     * @var array
     */
    protected array $attributes;

    /**
     * CreateNewDistrict constructor.
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
            'name' => 'required',
        ])->validate();
    }

    /**
     * Handle the job.
     *
     * @return bool
     */
    public function handle(): bool
    {
        $district = new District();
        $district->fill($this->attributes);

        if ($district->save()) {
            event(new DistrictModified($district, true));
        }

        return $district->exists;
    }
}
