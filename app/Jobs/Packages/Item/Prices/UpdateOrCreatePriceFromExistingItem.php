<?php

namespace App\Jobs\Packages\Item\Prices;

use App\Models\Packages\Item;
use App\Models\Packages\Price;
use Illuminate\Validation\Rule;
use App\Models\Packages\Package;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Bus\Dispatchable;

class UpdateOrCreatePriceFromExistingItem
{
    use Dispatchable;

    /**
     * @var Package
     */
    public Package $package;

    /**
     * @var Item
     */
    public Item $item;


    /**
     * @var Price
     */
    public Price $price;

    /**
     * @var array
     */
    private array $attributes;


    /**
     * @param \App\Models\Packages\Package $package
     * @param Item $item
     * @param array $inputs
     * @throws \Illuminate\Validation\ValidationException
     */
    public function __construct(Package $package, Item $item, array $inputs)
    {
        $this->attributes = Validator::make($inputs, [
            'type' => ['required', Rule::in(Price::getAvailableTypes())],
            'description' => ['required'],
            'amount' => ['required', 'numeric'],
        ])->validate();

        $this->item = $item;
        $this->package = $package;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->attributes['package_id'] = $this->package->id;

        /** @var Price $price */
        $price = $this->item->prices()->updateOrCreate([
            'package_id' => $this->package->id,
        ], $this->attributes);

        $this->price = $price;
    }
}
