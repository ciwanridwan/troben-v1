<?php

namespace App\Jobs\Partners\Prices;

use App\Models\Partners\Prices\Transit;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Validator;

class DeleteExistingTransit
{
    use Dispatchable;

    /**
     * Transit instance.
     *
     * @var Transit
     */
    public Transit $transitPrice;

    public array $attributes;

    /**
     * @param array $input
     * @throws \Illuminate\Validation\ValidationException
     */
    public function __construct(array $input)
    {
        $this->attributes = Validator::make($input, [
            'partner_id' => 'required',
            'origin_regency_id' => 'required',
            'destination_regency_id' => 'required',
            'type' => 'required',
            'shipment_type' => 'required'
        ])->validate();
    }

    /**
     * @return bool|null
     * @throws \Throwable
     */
    public function handle(): ?bool
    {
        return Transit::query()->where($this->attributes)->delete();
    }
}
