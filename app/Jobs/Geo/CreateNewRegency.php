<?php

namespace App\Jobs\Geo;

use App\Models\Geo\Regency;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use App\Events\Geo\RegencyModified;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class CreateNewRegency implements ShouldQueue
{
    use Dispatchable, SerializesModels, Queueable, InteractsWithQueue, Batchable;

    /**
     * Filtered attributes.
     *
     * @var array
     */
    protected array $attributes;

    /**
     * CreateNewRegency constructor.
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
            'name' => 'required',
            'capital' => 'nullable',
            'bsn_code' => 'nullable',
        ])->validate();
    }

    /**
     * Handle the job.
     *
     * @return bool
     */
    public function handle(): bool
    {
        $regency = new Regency();
        $regency->fill($this->attributes);

        if ($regency->save()) {
            event(new RegencyModified($regency, true));
        }

        return $regency->exists;
    }
}
