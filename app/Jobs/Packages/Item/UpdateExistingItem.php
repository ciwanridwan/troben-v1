<?php

namespace App\Jobs\Packages\Item;

use App\Events\Packages\PackageUpdated;
use App\Models\Handling;
use App\Models\Packages\Item;
use App\Models\Packages\Package;
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

        $this->item = $item;

        $this->attributes = Validator::make($inputs, [
            'qty' => ['nullable', 'numeric'],
            'name' => 'nullable',
            'desc' => 'nullable',
            'weight' => ['nullable', 'numeric'],
            'height' => ['nullable', 'numeric'],
            'length' => ['nullable', 'numeric'],
            'width' => ['nullable', 'numeric'],
            'handling' => ['nullable', 'array'],
            'handling.*' => ['numeric', 'exists:handling,id'],
            'is_insured' => ['nullable', 'boolean'],
        ])->validated();
    }

    public function handle()
    {
        if (array_key_exists('handling', $this->attributes)) {
            $this->attributes['handling'] = collect($this->attributes['handling'])
                ->map(fn ($id) => Handling::query()->find($id))
                ->filter(fn (?Handling $handling) => $handling !== null)
                ->toArray();
        }

        $this->item->fill($this->attributes);

        $this->item->save();

        event(new PackageUpdated($this->item->package));
    }
}
