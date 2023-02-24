<?php

namespace App\Jobs\Packages\Item;

use Illuminate\Validation\Rule;
use App\Models\Packages\Package;
use App\Casts\Package\Items\Handling;
use App\Events\Packages\CustomerServices\PackageUpdatedByCs;
use App\Events\Packages\PackageUpdated;
use App\Models\Packages\CategoryItem;
use App\Models\Packages\Item;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Collection;

class UpdateExistingOrCreateNewItemByCs
{
    use Dispatchable;

    public Package $package;

    public Collection $items;

    private array $attributes;

    /**
     * DeleteItemFromExistingPackage constructor.
     * @param \App\Models\Packages\Package $package
     * @param array $inputs
     * @throws \Throwable
     */
    public function __construct(Package $package, array $inputs)
    {
        $this->package = $package;
        $this->items = $this->package->items;

        $this->attributes = Validator::make($inputs, [
            '*.hash' => ['nullable'],
            '*.category_item_id' => ['numeric'], //disable validation for compability old package
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
    }

    public function handle()
    {
        $this->items->each(function ($r) {
            $r->delete();
        });

        foreach ($this->attributes as $attr) {
            $this->caseInsertItem($this->package, $attr);
        }

        event(new PackageUpdatedByCs($this->package));
        // event(new PackageUpdated($this->package)); //taken from job CreateNewItemByCs, skip it
    }

    protected function caseInsertItem(Package $package, $payload)
    {
        if (isset($payload['category_item_id'])) {
            $checkCategory = CategoryItem::where('id', $payload['category_item_id'])->first();
            if (is_null($checkCategory)) {
                unset($payload['category_item_id']);
            }
        }

        $payload['package_id'] = $package->getKey();
        Item::create($payload);
    }

    protected function caseUpdateItem(Item $item, $payload)
    {
        if (isset($payload['category_item_id'])) {
            $checkCategory = CategoryItem::where('id', $payload['category_item_id'])->first();
            if (is_null($checkCategory)) {
                unset($payload['category_item_id']);
            }
        }

        // do save item
        $item->fill($payload);
        $item->save();
    }
}
