<?php

namespace App\Events\Inventory;

use App\Models\Partners\Inventory;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ManyInventoryCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Invevntory instance.
     *
     * @var \App\Models\Partners\Inventory
     */
    public Inventory $inventory;

    /**
     * Create a new event instance.
     *
     * @param \App\Models\Partners\Inventory $inventory
     */
    public function __construct(Inventory $inventory)
    {
        $this->inventory = $inventory;
    }
}
