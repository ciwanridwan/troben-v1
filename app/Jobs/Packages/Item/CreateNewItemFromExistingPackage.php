<?php

namespace App\Jobs\Packages\Item;

use App\Models\Packages\Item;
use Illuminate\Validation\Rule;
use App\Models\Packages\Package;
use App\Casts\Package\Items\Handling;
use App\Events\Packages\PackageUpdated;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Bus\Dispatchable;

class CreateNewItemFromExistingPackage
{
    use Dispatchable;

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
            'qty' => ['required', 'numeric'],
            'name' => 'required',
            'desc' => 'nullable',
            'weight' => ['required', 'numeric'],
            'height' => ['required', 'numeric'],
            'length' => ['required', 'numeric'],
            'width' => ['required', 'numeric'],
            'price' => ['required_if:is_insured,true', 'numeric'],
            'is_insured' => ['nullable', 'boolean'],
            'handling' => ['nullable', 'array'],
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
        $item = $this->package->items()->create($this->attributes);

        $this->item = $item;

        event(new PackageUpdated($this->item->package));
    }
}
