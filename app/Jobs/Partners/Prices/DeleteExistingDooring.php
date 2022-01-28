<?php

namespace App\Jobs\Partners\Prices;

use App\Models\Partners\Prices\Dooring;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Validator;

class DeleteExistingDooring
{
    use Dispatchable;

    protected array $attributes;

    /**
     * @param array $input
     * @throws \Illuminate\Validation\ValidationException
     */
    public function __construct(array $input)
    {
        $this->attributes = Validator::make($input, [
            'partner_id' => 'required',
            'origin_regency_id' => 'required',
            'destination_sub_district_id' => 'required',
            'type' => 'required',
        ])->validate();
    }

    /**
     * @return mixed
     */
    public function handle()
    {
        return Dooring::query()->where($this->attributes)->delete();
    }
}
