<?php

namespace App\Jobs\Packages\Item;

use App\Models\Packages\Item;
use App\Models\Packages\Package;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Validation\ValidationException;

class DeleteItemFromExistingPackage
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
     * @throws \Throwable
     */
    public function __construct(Package $package, Item $item)
    {
        throw_if($item->package_id != $package->id, ValidationException::withMessages([
            'item' => __(':item is not part of the given package', [
                'item' => $item->name,
            ]),
        ]));

        $this->item = $item;
    }

    /**
     * @throws \Exception
     */
    public function handle()
    {
        $this->item->delete();
    }
}
