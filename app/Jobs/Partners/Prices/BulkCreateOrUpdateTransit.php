<?php

namespace App\Jobs\Partners\Prices;

use App\Models\Partners\Partner;
use App\Models\Partners\Prices\Transit;
use App\Models\Price;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Validator;

class BulkCreateOrUpdateTransit
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
     * Create or Update partner transit price construct.
     * @param array $inputs
     * @throws \Illuminate\Validation\ValidationException
     */
    public function __construct(array $inputs)
    {
        $this->query = Transit::query();
        $this->attributes = Validator::make($inputs, [
            '*.partner_id' => ['required','exists:partners,id'],
            '*.origin_regency_id' => ['required','exists:geo_regencies,id'],
            '*.destination_regency_id' => ['required','exists:geo_regencies,id'],
            '*.type' => ['required','numeric'],
            '*.value' => ['required','numeric'],
            '*.shipment_type' => ['nullable','numeric'],
        ])->validate();
    }

    /**
     * @throws \Exception
     */
    public function handle()
    {
        try {
            $this->query->upsert($this->attributes, [
                'partner_id',
                'origin_regency_id',
                'destination_regency_id',
                'type',
                'shipment_type'
            ],['value']);
        } catch (\Exception $e) {
            throw new \Exception('Please check your data, specially on '.Partner::find($this->attributes['partner_id'])->code);
        }
    }
}
