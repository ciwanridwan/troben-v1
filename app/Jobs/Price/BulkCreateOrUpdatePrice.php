<?php

namespace App\Jobs\Price;

use App\Models\Price;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Validator;

class BulkCreateOrUpdatePrice
{
    use Dispatchable;

    /**
     * Base Query.
     *
     * @var Builder $query
     */
    public Builder $query;

    /**
     * Filtered Attributes.
     *
     * @var array $attributes
     */
    public array $attributes;

    /**
     * Bulk update or create
     * @param array $inputs
     * @throws \Illuminate\Validation\ValidationException
     */
    public function __construct(array $inputs)
    {
        $this->query = Price::query();
        $this->attributes = Validator::make($inputs,[
            '*.origin_province_id' => ['required','exists:geo_provinces,id'],
            '*.origin_regency_id' => ['required','exists:geo_regencies,id'],
            '*.origin_district_id' => ['nullable','exists:geo_districts,id'],
            '*.origin_sub_district_id' => ['nullable','exists:geo_sub_districts,id'],
            '*.destination_id' => ['required','exists:geo_sub_districts,id'],
            '*.zip_code' => ['required','exists:geo_sub_districts,zip_code'],
            '*.service_code' => ['required','exists:services,code'],
            '*.tier_1' => ['nullable','numeric'],
            '*.tier_2' => ['nullable','numeric'],
            '*.tier_3' => ['nullable','numeric'],
            '*.tier_4' => ['nullable','numeric'],
            '*.tier_5' => ['nullable','numeric'],
            '*.tier_6' => ['nullable','numeric'],
            '*.tier_7' => ['nullable','numeric'],
            '*.tier_8' => ['nullable','numeric'],
            '*.tier_9' => ['nullable','numeric'],
            '*.tier_10' => ['nullable','numeric'],
            '*.notes' => ['nullable','string']
        ])->validate();
    }

    /**
     * Execute bulk create or update price
     */
    public function handle(): void
    {
        $this->query->upsert($this->attributes, [
            'origin_regency_id',
            'destination_id'
        ], [
            'tier_1',
            'tier_2',
            'tier_3',
            'tier_4',
            'tier_5',
            'tier_6',
            'tier_7',
            'tier_8',
            'notes',
        ]);
    }
}
