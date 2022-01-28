<?php

namespace App\Jobs\Partners\Prices;

use App\Models\Partners\Partner;
use App\Models\Partners\Prices\Dooring;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BulkCreateOrUpdateDooring
{
    use Dispatchable;

    protected Builder $query;

    protected array $attributes;

    /**
     * @param array $inputs
     * @throws \Illuminate\Validation\ValidationException
     */
    public function __construct(array $inputs)
    {
        $this->query = Dooring::query();
        $this->attributes = Validator::make($inputs, [
            '*.partner_id' => ['required','exists:partners,id'],
            '*.origin_regency_id' => ['required','exists:geo_regencies,id'],
            '*.destination_sub_district_id' => ['required','exists:geo_sub_districts,id'],
            '*.type' => ['required','numeric'],
            '*.value' => ['required','numeric'],
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
                'destination_sub_district_id',
                'type',
            ], ['value']);
        } catch (\Exception $e) {
            Log::error('error', [$e->getMessage()]);
            throw new \Exception('Please check your data, specially on '.Partner::find($this->attributes[0]['partner_id'])->code);
        }
    }
}
