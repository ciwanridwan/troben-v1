<?php

namespace App\Jobs\Price;

use App\Models\Price;
use Illuminate\Bus\Batchable;
use App\Events\Price\PriceModified;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Events\Price\PriceModificationFailed;

class UpdateExistingPrice
{
    use Dispatchable, InteractsWithQueue, SerializesModels, Batchable;

    /**
     * Price instance.
     *
     * @var \App\Models\Price
     */
    public Price $price;

    /**
     * Filtered attributes.
     *
     * @var array
     */
    public array $attributes;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Price $price, $inputs = [])
    {
        $this->price = $price;
        $this->attributes = Validator::make($inputs, [
            'origin_province_id' => ['filled','exists:geo_provinces,id'],
            'origin_regency_id' => ['filled','exists:geo_regencies,id'],
            'origin_district_id' => ['nullable','exists:geo_districts,id'],
            'origin_sub_district_id' => ['nullable','exists:geo_sub_districts,id'],
            'destination_id' => ['filled','exists:geo_sub_districts,id'],
            'zip_code' => ['filled','exists:geo_sub_districts,zip_code'],
            'service_code' => ['filled','exists:services,code'],
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
     * Update Existing Price Job.
     *
     * @return void
     */
    public function handle(): bool
    {
        collect($this->attributes)->each(fn ($v, $k) => $this->price->{$k} = $v);

        if ($this->price->isDirty() && $this->price->save()) {
            event(new PriceModified($this->price));
        } else {
            event(new PriceModificationFailed($this->price));
        }

        return $this->price->exists;
    }
}
