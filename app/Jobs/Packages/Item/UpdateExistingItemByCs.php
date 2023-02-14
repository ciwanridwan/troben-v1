<?php

namespace App\Jobs\Packages\Item;

use Illuminate\Validation\Rule;
use App\Models\Packages\Package;
use App\Casts\Package\Items\Handling;
use App\Events\Packages\CustomerServices\PackageUpdatedByCs;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Collection;

class UpdateExistingItemByCs
{
    use Dispatchable;

    /**
     * @var \App\Models\Packages\Item
     */
    public Collection $item;

    public Package $package;

    private array $attributes;

    /**
     * DeleteItemFromExistingPackage constructor.
     * @param \App\Models\Packages\Package $package
     * @param \App\Models\Packages\Item $item
     * @param array $inputs
     * @throws \Throwable
     */
    public function __construct(Package $package, array $inputs)
    {
        $this->package = $package;
        $this->item = $this->package->items;
        $this->attributes = $inputs['items'];
    }

    public function handle()
    {
        foreach ($this->attributes as $attr) {
            $this->item->each(function ($q) use ($attr) {
                $q->fill($attr);
                $q->save();
            });
        }

        event(new PackageUpdatedByCs($this->package));
    }
}
