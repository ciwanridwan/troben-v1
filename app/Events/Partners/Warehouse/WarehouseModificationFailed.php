<?php

namespace App\Events\Partners\Warehouse;

use App\Models\Partners\Warehouse;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class WarehouseModificationFailed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Warehouse $warehouse;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Warehouse $warehouse)
    {
        $this->warehouse = $warehouse;
    }
}
