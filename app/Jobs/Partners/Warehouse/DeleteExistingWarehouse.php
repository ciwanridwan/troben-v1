<?php

namespace App\Jobs\Partners\Warehouse;

use Illuminate\Bus\Batchable;
use App\Models\Partners\Warehouse;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Events\Partners\Warehouse\WarehouseDeleted;

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
     * Soft delete flag.
     *
     * @var bool
     */
    public bool $softDelete;

    /**
     * DeleteExistingWarehouse construct.
     *
     * @param \App\Models\Partners\Warehouse $warehouse
     * @param bool                           $force
     */
    public function __construct(Warehouse $warehouse, $force = false)
    {
        $this->warehouse = $warehouse;
        $this->softDelete = ! $force;
    }

    /**
     * Delete Warehouse job.
     *
     * @return bool
     * @throws \Exception
     */
    public function handle(): bool
    {
        $result = $this->softDelete ? $this->warehouse->delete() : $this->warehouse->forceDelete();

        event(new WarehouseDeleted($this->warehouse));

        return (bool) $result;
    }
}
