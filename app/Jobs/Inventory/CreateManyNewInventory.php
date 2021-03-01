<?php

namespace App\Jobs\Inventory;

use App\Events\Inventory\ManyInventoryCreated;
use App\Models\Partners\Inventory;
use Illuminate\Bus\Batchable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Validator;

class CreateManyNewInventory
{
    use Dispatchable, InteractsWithQueue, SerializesModels, Batchable;

    /**
     * Inventory instance.
     *
     * @var \App\Models\Partners\Inventory
     */
    public Inventory $inventory;

    /**
     * Filtered Attributes.
     *
     * @var array
     */
    public array $attributes;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($inputs = [])
    {
        $this->inventory = new Inventory();
        $this->attributes = Validator::make($inputs, [
            'name.*' => ['required','string','max:255'],
            'capacity.*' => ['required','numeric'],
            'height.*' => ['required','numeric'],
            'count.*' => ['required','numeric']
        ])->validate();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->inventory->createMany($this->attributes);

        if ($this->inventory->save()) {
            event(new ManyInventoryCreated($this->inventory));
        }

        return $this->inventory->exists;
    }
}
