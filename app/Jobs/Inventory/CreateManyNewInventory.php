<?php

namespace App\Jobs\Inventory;

use Illuminate\Bus\Batchable;
use App\Models\Partners\Partner;
use App\Models\Partners\Inventory;
use Illuminate\Support\Collection;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Events\Inventory\ManyInventoryCreated;
use App\Models\Partners\Warehouse;

class CreateManyNewInventory
{
    use Dispatchable, InteractsWithQueue, SerializesModels, Batchable;

    /**
     * Partner instance.
     *
     * @var \App\Models\Partners\Partner
     */
    public Partner $partner;

    /**
     * Collection inventories.
     *
     * @var Illuminate\Support\Collection|App\Models\Partners\Inventory $inventories
     */
    public Collection $inventories;

    /**
     * Filtered Attributes.
     *
     * @var array
     */
    public array $attributes;

    /**
     * Flag jobs done.
     *
     * @var bool
     */
    public bool $finish = false;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Partner $partner, $inputs = [])
    {
        $this->partner = $partner;
        $this->attributes = Validator::make($inputs, [
            '*.name' => ['required', 'string', 'max:255'],
            '*.capacity' => ['required', 'numeric'],
            '*.height' => ['required', 'numeric'],
            '*.qty' => ['required', 'numeric'],
            '*.warehouse_id' => ['nullable', 'exists:warehouses,id']
        ])->validate();
    }

    /**
     * @return bool
     */
    public function handle(): bool
    {
        $this->inventories = $this->partner->inventories()->createMany($this->attributes);

        // if ($this->warehouse) {
        //     $this->inventories->each(fn (Inventory $inventory) => $inventory->setAttribute('warehouse_id', $this->warehouse->id)->save());
        // }

        if ($this->inventories) {
            event(new ManyInventoryCreated($this->partner->inventories));
        }

        if (Inventory::whereIn('id', collect($this->inventories)->pluck('id'))->count() == $this->inventories->count()) {
            $this->finish = true;
        }

        return $this->finish;
    }
}
