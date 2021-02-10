<?php

namespace App\Jobs\Services;

use App\Events\Services\ServiceDeleted;
use App\Models\Service;
use Illuminate\Bus\Batchable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DeleteExistingService
{
    use Dispatchable, InteractsWithQueue, SerializesModels, Batchable;

    /**
     * Service instance.
     * 
     * @var \App\Models\Service
     */
    public Service $service;

    /**
     * Soft delete flag.
     * 
     * @var bool
     */
    public bool $softDelete;

    /**
     * DeleteExistingService constructor.
     *
     * @return void
     */
    /**
     * @param \App\Models\Service $service
     * @param bool $force
     */
    public function __construct(Service $service, $force = false)
    {
        $this->service = $service;
        $this->softDelete = ! $force;
    }

    /**
     * Execute DeleteExistingService job.
     *
     * @return void
     */
    public function handle(): bool
    {
        (bool) $result = $this->softDelete ? $this->service->delete() : $this->service->forceDelete();

        event(new ServiceDeleted($this->service));

        return $result;
    }
}
