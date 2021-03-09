<?php

namespace App\Jobs\Packages;

use App\Models\Packages\Package;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Bus\Dispatchable;

class CreateNewPackage
{
    use Dispatchable;

    /**
     * Package instance.
     *
     * @var \App\Models\Packages\Package
     */
    public Package $package;

    /**
     * Package attributes.
     *
     * @var array
     */
    protected array $attributes;

    /**
     * Package items array.
     *
     * @var array
     */
    protected array $items;

    /**
     * Item separation flag.
     *
     * @var bool
     */
    protected bool $is_separate;

    /**
     * CreateNewPackage constructor.
     *
     * @param array $inputs
     * @param array $items
     * @param bool  $isSeparate
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function __construct(array $inputs = [], array $items = [], bool $isSeparate = false)
    {
        $this->attributes = Validator::make($inputs, [
            'customer_id' => [ 'required', 'exists:customers,id' ],
            'service_code' => ['required', 'exists:services,code' ],
            'receiver_name' => ['required'],
            'receiver_phone' => ['required'],
            'receiver_address' => ['required'],
            'geo_regency_id' => ['required'],
            'geo_district_id' => ['required'],
            'geo_sub_district_id' => ['required']
        ])->validate();

        $this->items = Validator::make($items, [
            '*.qty' => 'required',
            '*.name' => 'required',
            '*.desc' => 'nullable',
            '*.weight' => 'required',
            '*.height' => 'required',
            '*.length' => 'required',
            '*.width' => 'required',
        ])->validate();

        $this->is_separate = $isSeparate;
        $this->package = new Package();
    }

    public function handle()
    {

    }
}
