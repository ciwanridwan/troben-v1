<?php

namespace App\Jobs\Geo;

use App\Models\Geo\Province;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use App\Events\Geo\ProvinceModified;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class CreateNewProvince implements ShouldQueue
{
    use Dispatchable, SerializesModels, Queueable, InteractsWithQueue, Batchable;

    /**
     * Filtered attributes.
     *
     * @var array
     */
    protected array $attributes;

    /**
     * CreateNewProvince constructor.
     *
     * @param array $inputs
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function __construct($inputs = [])
    {
        $this->attributes = Validator::make($inputs, [
            'country_id' => 'required|exists:geo_countries,id',
            'name' => 'required',
            'iso_code' => 'required',
        ])->validate();
    }

    /**
     * Handle the job.
     *
     * @return bool
     */
    public function handle(): bool
    {
        $province = new Province();
        $province->fill($this->attributes);

        if ($province->save()) {
            event(new ProvinceModified($province, true));
        }

        return $province->exists;
    }
}
