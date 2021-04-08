<?php

namespace App\Jobs\Packages\Item\Prices;

use App\Models\Packages\Item;
use App\Models\Packages\Package;
use App\Models\Packages\Price;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CreateNewPriceFromExistingItem
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
     * @var array
     */
    private array $attributes;


    /**
     * @var Price
     */
    public Price $price;


    /**
     * @param Item $item
     * @param array $inputs
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
        $price = $this->item->prices()->create($this->attributes);

        $this->price = $price;

        return $this->price->exists;
    }
}
