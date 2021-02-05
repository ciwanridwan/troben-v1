<?php

namespace App\Jobs\Partners\Warehouse;

use App\Events\Partners\Warehouse\WarehouseDeleted;
use App\Models\Partners\Warehouse;
use Illuminate\Bus\Batchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class DeleteExistingWarehouse
{
    use Dispatchable, InteractsWithQueue, Batchable, SerializesModels;

    /**
     * Warehouse instance.
     * 
     * @var \App\Models\Partners\Warehouse
     */
    public Warehouse $warehouse;

    /**
     * DeleteExistingWarehouse construct.
     * 
     * @param \App\Models\Partners\Warehouse $warehouse
     */
    public function __construct(Warehouse $warehouse)
    {
        $this->warehouse = $warehouse;
    }

    /**
     * Delete Warehouse job.
     * 
     * @return bool
     */
    public function handle(): bool
    {
        if ($this->warehouse->delete()) {
            event(new WarehouseDeleted($this->warehouse));
        }

        return $this->warehouse->deleted_at !== null;
    }
}
