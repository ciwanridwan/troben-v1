<?php

namespace App\Events\Partners\Warehouse;

use App\Models\Partners\Warehouse;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WarehouseModified
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
