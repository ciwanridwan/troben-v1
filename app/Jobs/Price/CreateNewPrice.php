<?php

namespace App\Jobs\Price;

use App\Events\Price\NewPriceCreated;
use App\Models\Price;
use Illuminate\Bus\Batchable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Validator;

class CreateNewPrice
{
    use Dispatchable, InteractsWithQueue, SerializesModels, Batchable;

    /**
     * Price instance.
     *
     * @var \App\Models\Price
     */
    public Price $price;

    /**
     * Filtered Attributes.
     *
     * @var array
     */
    public array $attributes;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($inputs = [])
    {
        $this->price = new Price();
        $this->attributes = Validator::make($inputs, [
            'origin_province_id' => ['required','exists:geo_provinces,id'],
            'origin_regency_id' => ['required','exists:geo_regencies,id'],
            'origin_district_id' => ['nullable','exists:geo_districts,id'],
            'origin_sub_district_id' => ['nullable','exists:geo_sub_districts,id'],
            'destination_id' => ['required','exists:geo_sub_districts,id'],
            'zip_code' => ['required','exists:geo_sub_districts,zip_code'],
            'service_code' => ['required','exists:services,code'],
            'tier_1' => ['nullable','numeric'],
            'tier_2' => ['nullable','numeric'],
            'tier_3' => ['nullable','numeric'],
            'tier_4' => ['nullable','numeric'],
            'tier_5' => ['nullable','numeric'],
            'tier_6' => ['nullable','numeric'],
            'tier_7' => ['nullable','numeric'],
            'tier_8' => ['nullable','numeric'],
            'tier_9' => ['nullable','numeric'],
            'tier_10' => ['nullable','numeric'],
        ])->validate();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->price->fill($this->attributes);

        if ($this->price->save()) {
            event(new NewPriceCreated($this->price));
        }

        return $this->price->exists;
    }
}
