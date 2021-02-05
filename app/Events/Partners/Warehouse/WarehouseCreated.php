<?php

namespace App\Events\Partners\Warehouse;

use App\Models\Partners\Warehouse;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class WarehouseCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Instance Warehouse.
     *
     * @var App\Models\Partners\Warehouse
     *
     */
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
