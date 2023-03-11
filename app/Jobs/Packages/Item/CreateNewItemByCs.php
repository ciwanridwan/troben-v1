<?php

namespace App\Jobs\Packages\Item;

use App\Models\Packages\Item;
use Illuminate\Validation\Rule;
use App\Models\Packages\Package;
use App\Casts\Package\Items\Handling;
use App\Events\Packages\PackageUpdated;
use Illuminate\Support\Facades\Validator;

class CreateNewItemByCs
{
    /**
     * @var \App\Models\Packages\Item
     */
    public Item $item;

    private array $attributes;

    /**
     * @var \App\Models\Packages\Package
     */
    private Package $package;

    /**
     * DeleteItemFromExistingPackage constructor.
     * @param \App\Models\Packages\Package $package
     * @param array $inputs
     * @throws \Throwable
     */
    public function __construct(Package $package, array $inputs)
    {
        $this->attributes = Validator::make($inputs, [
            '*.category_item_id' => ['numeric', 'exists:category_items,id'],
            '*.is_glassware' => ['boolean'],
            '*.qty' => ['numeric'],
            '*.name' => ['string'],
            '*.price' => ['required_if:is_insured,true', 'numeric'],
            '*.desc' => ['string'],
            '*.weight' => ['numeric'],
            '*.height' => ['numeric'],
            '*.length' => ['numeric'],
            '*.width' => ['numeric'],
            '*.is_insured' => ['boolean'],
            '*.handling' => ['nullable', 'array'],
            '*.handling.*' => ['string', Rule::in(Handling::getTypes())],
        ])->validate();

        $this->package = $package;
    }

    /**
     * @throws \Exception
     */
    public function handle()
    {
        /** @var Item $item */
        foreach ($this->attributes as $attribute) {
            $item = $this->package->items()->create($attribute);
            $this->item = $item;

            event(new PackageUpdated($this->item->package));
        }
    }
}
