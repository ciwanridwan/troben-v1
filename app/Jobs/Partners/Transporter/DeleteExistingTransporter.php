<?php

namespace App\Jobs\Partners\Transporter;

use Illuminate\Bus\Batchable;
use App\Models\Partners\Transporter;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Events\Partners\Transporter\TransporterDeleted;

class DeleteExistingTransporter
{
    use Dispatchable, InteractsWithQueue, Batchable, SerializesModels;

    /**
     * Partner instance.
     * @var \App\Models\Partners\Transporter
     */
    public Transporter $transporter;

    /**
     * Soft delete flag.
     * 
     * @var bool
     */
    public bool $softDelete;

    /**
     * DeleteExistingTransporter constructor.
     *
     * @param \App\Models\Partners\Transporter $transporter
     * @param bool                             $force
     */
    public function __construct(Transporter $transporter, $force = false)
    {
        $this->transporter = $transporter;
        $this->softDelete = ! $force;
    }

    /**
     * Delete Transporter job.
     * 
     * @return bool
     * @throws \Exception
     */
    public function handle(): bool
    {
        $result = $this->softDelete
            ? $this->transporter->delete()
            : $this->transporter->forceDelete();

        event(new TransporterDeleted($this->transporter));

        return (bool) $result;
    }
}
