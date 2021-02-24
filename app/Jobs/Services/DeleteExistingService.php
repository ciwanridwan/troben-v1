<?php

namespace App\Jobs\Services;

use App\Models\Service;
use Illuminate\Bus\Batchable;
use Illuminate\Queue\SerializesModels;
use App\Events\Services\ServiceDeleted;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;

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
     * @param \App\Models\Service $service
     * @param bool $force
     *
     * @return void
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
