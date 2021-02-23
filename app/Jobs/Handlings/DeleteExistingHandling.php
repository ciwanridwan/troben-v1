<?php

namespace App\Jobs\Handlings;

use App\Events\Handlings\HandlingDeleted;
use App\Models\Handling;
use Illuminate\Bus\Batchable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DeleteExistingHandling
{
    use Dispatchable, InteractsWithQueue, SerializesModels, Batchable;

    /**
     * Handling instance.
     *
     * @var \App\Models\Handling
     */
    public Handling $handling;

    /**
     * Softdelete flag.
     *
     * @var bool
     */
    public bool $softDelete;

    /**
     * DeleteExistingHandling constructor
     *
     * @param \App\Models\Handling  $handling
     * @param bool                  $force
     */
    public function __construct(Handling $handling, $force = false)
    {
        $this->handling = $handling;
        $this->softDelete = ! $force;
    }

    /**
     * Execute the job.
     *
     * @return bool
     */
    public function handle(): bool
    {
        (bool) $result = $this->softDelete ? $this->handling->delete() : $this->handling->forceDelete();

        event(new HandlingDeleted($this->handling));

        return $result;
    }
}
