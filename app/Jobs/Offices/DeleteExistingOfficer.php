<?php

namespace App\Jobs\Offices;

use App\Events\Offices\OfficerDeleted;
use App\Models\Offices\Office;
use Illuminate\Bus\Batchable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DeleteExistingOfficer
{
    use Dispatchable, SerializesModels, InteractsWithQueue, Batchable;

    /**
     * Office instance.
     *
     * @var Office
     */
    public Office $office;

    /**
     * Soft Delete flag.
     *
     * @var bool
     */
    public bool $softDelete;

    /**
     * DeleteExistingCustomer constructor.
     *
     * @param Office $office
     * @param bool                           $force
     */
    public function __construct(Office $office, $force = false)
    {
        $this->office = $office;
        $this->softDelete = ! $force;
    }

    /**
     * Handle the job.
     *
     * @return bool
     * @throws \Exception
     */
    public function handle(): bool
    {
        (bool) $result = $this->softDelete ? $this->office->delete() : $this->office->forceDelete();

        event(new OfficerDeleted($this->office));

        return $result;
    }
}
