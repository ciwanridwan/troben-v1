<?php

namespace App\Events\Inventory;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class ManyInventoryCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Invevntory instance.
     *
     * @var Illuminate\Support\Collection|\App\Models\Partners\Inventory
     */
    public Collection $inventory;

    /**
     * Create a new event instance.
     *
     * @param Illuminate\Support\Collection|\App\Models\Partners\Inventory $inventory
     */
    public function __construct(Collection $inventory)
    {
        $this->inventory = $inventory;
    }
}
