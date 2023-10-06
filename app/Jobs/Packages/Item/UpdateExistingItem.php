<?php

namespace App\Jobs\Packages\Item;

use App\Models\Packages\Item;
use Illuminate\Validation\Rule;
use App\Models\Packages\Package;
use App\Casts\Package\Items\Handling;
use App\Events\Packages\PackageUpdated;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Validation\ValidationException;

class UpdateExistingItem
{
    use Dispatchable;

    /**
     * @var \App\Models\Packages\Item
     */
    public Item $item;

    public Package $package;

    private array $attributes;

    /**
     * DeleteItemFromExistingPackage constructor.
     * @param \App\Models\Packages\Package $package
     * @param \App\Models\Packages\Item $item
     * @param array $inputs
     * @throws \Throwable
     */
    public function __construct(Package $package, Item $item, array $inputs)
    {
        throw_if($item->package_id != $package->id, ValidationException::withMessages([
            'item' => __(':item is not part of the given package', [
                'item' => $item->name,
            ]),
        ]));

        $this->package = $package;
        $this->item = $item;
        $this->attributes = Validator::make($inputs, [
            'qty' => ['nullable', 'numeric'],
            'name' => 'nullable',
            'desc' => 'nullable',
            'weight' => ['nullable', 'numeric'],
            'height' => ['nullable', 'numeric'],
            'length' => ['nullable', 'numeric'],
            'width' => ['nullable', 'numeric'],
            'price' => ['required_if:is_insured,true', 'numeric'],
            'handling' => ['nullable', 'array'],
            '*.handling.*' => ['string', Rule::in(Handling::getTypes())],
            'is_insured' => ['nullable', 'boolean'],
        ])->validate();
    }

    public function handle()
    {
        \Log::info('itemschanges', ['pid' => $this->package->getKey(), 'attr' => $this->attributes]);

        if (!isset($this->attributes['qty']) || $this->attributes['qty'] == null) {
            $this->attributes['qty'] = 0;
        }
        if (isset($this->attributes['qty']) && $this->attributes['qty'] == 0) {
            // remove 0 qty item
            unset($this->attributes['qty']);
        }
        //        if (count($this->attributes['handling']) < 1) {
        //            $this->attributes['handling'] = null;
        //        }

        //remove from filled data if null
        foreach (['width', 'height', 'length', 'weight'] as $k) {
            if (isset($this->attributes[$k]) && is_null($this->attributes[$k])) {
                unset($this->attributes[$k]);
            }
        }

        $this->item->fill($this->attributes);
        $this->item->fill([
            'revision' => $this->item->revision + 1
        ]);
        $this->item->save();

        event(new PackageUpdated($this->item->package));
        return $this->item->exists;
    }
}
