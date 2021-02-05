<?php

namespace App\Events\Partners\Warehouse;

use App\Models\Partners\Warehouse;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WarehouseDeleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Warehouse instance.
     * 
     * @var \App\Models\Partners\Warehouse
     */
    public Warehouse $warehouse;

    /**
     * WarehouseDeleted construct.
     * 
     * @param \App\Models\Partners\Warehouse $warehouse
     */
    public function __construct(Warehouse $warehouse)
    {
        $this->warehouse = $warehouse;
    }
}
