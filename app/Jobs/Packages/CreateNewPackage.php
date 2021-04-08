<?php

namespace App\Jobs\Packages;

use App\Models\Packages\Item;
use Illuminate\Validation\Rule;
use App\Models\Packages\Package;
use App\Models\Partners\Transporter;
use App\Casts\Package\Items\Handling;
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
    protected bool $isSeparate;

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
            'customer_id' => ['required', 'exists:customers,id'],
            'service_code' => ['required', 'exists:services,code'],
            'transporter_type' => ['required', Rule::in(Transporter::getAvailableTypes())],
            'sender_name' => ['required'],
            'sender_phone' => ['required'],
            'sender_address' => ['required'],
            'receiver_name' => ['required'],
            'receiver_phone' => ['required'],
            'receiver_address' => ['required'],
            'handling' => ['nullable', 'array'],
            'handling.*' => ['string', Rule::in(Handling::getTypes())],
            'origin_regency_id' => ['required'],
            'destination_regency_id' => ['required'],
            'destination_district_id' => ['required'],
            'destination_sub_district_id' => ['required'],
        ])->validate();

        $this->items = Validator::make($items, [
            '*.qty' => ['required', 'numeric'],
            '*.name' => 'required',
            '*.desc' => 'nullable',
            '*.weight' => ['required', 'numeric'],
            '*.height' => ['required', 'numeric'],
            '*.length' => ['required', 'numeric'],
            '*.width' => ['required', 'numeric'],
            '*.is_insured' => ['nullable', 'boolean'],
            '*.handling' => ['nullable', 'array'],
            '*.handling.*' => ['string', Rule::in(Handling::getTypes())],
        ])->validate();

        $this->isSeparate = $isSeparate;
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
        $this->package->is_separate_item = $this->isSeparate;
        $this->package->save();

        if ($this->package->exists) {
            foreach ($this->items as $attributes) {
                $item = new Item();

                $attributes['package_id'] = $this->package->id;

                $item->fill($attributes);
                $item->save();
            }

            event(new PackageCreated($this->package));
        }

        return $this->package->exists;
    }
}
