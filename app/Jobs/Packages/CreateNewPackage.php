<?php

namespace App\Jobs\Packages;

use App\Models\Packages\Item;
use App\Models\Packages\Package;
use App\Events\Packages\PackageCreated;
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
    public function __construct(array $inputs, array $items, bool $isSeparate = false)
    {
        $this->attributes = Validator::make($inputs, [
            'customer_id' => [ 'required', 'exists:customers,id' ],
            'service_code' => ['required', 'exists:services,code' ],
            'receiver_name' => ['required'],
            'receiver_phone' => ['required'],
            'receiver_address' => ['required'],
            'origin_regency_id' => ['required'],
            'origin_district_id' => ['required'],
            'origin_sub_district_id' => ['required'],
            'destination_regency_id' => ['required'],
            'destination_district_id' => ['required'],
            'destination_sub_district_id' => ['required']
        ])->validate();

        $this->items = Validator::make($items, [
            '*.qty' => ['required', 'number'],
            '*.name' => 'required',
            '*.desc' => 'nullable',
            '*.weight' => ['required', 'number'],
            '*.height' => ['required', 'number'],
            '*.length' => ['required', 'number'],
            '*.width' => ['required', 'number'],
            '*.handling' => [ 'required', 'array']
        ])->validate();

        $this->is_separate = $isSeparate;
        $this->package = new Package();
    }

    /**
     * Handle the job.
     *
     * @return bool
     */
    public function handle(): bool
    {
        $this->package->fill($this->attributes);
        $this->package->is_separate_item = $this->is_separate;
        $this->package->save();

        if ($this->package->exists) {
            foreach ($this->items as $attribute) {
                $item = new Item();
                $item->fill(array_merge(['package_id' => $this->package->id], $attribute));
                $item->save();
            }

            event(new PackageCreated($this->package));
        }

        return $this->package->exists;
    }
}