<?php

namespace App\Jobs\Partners\SchedulesTransportation;

use App\Events\Partners\SchedulesTransportation\ScheduleDeleted;
use App\Models\Partners\ScheduleTransportation;
use Illuminate\Bus\Batchable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DeleteExistingSchedules
{
    use Dispatchable, SerializesModels, InteractsWithQueue, Batchable;

    /**
     * ScheduleTransportation instance.
     *
     * @var ScheduleTransportation
     */
    public ScheduleTransportation $scheduleTransportation;

    /**
     * Soft Delete flag.
     *
     * @var bool
     */
    public bool $softDelete;

    /**
     * DeleteExistingCustomer constructor.
     *
     * @param ScheduleTransportation $scheduleTransportation
     * @param bool $force
     */
    public function __construct(ScheduleTransportation $scheduleTransportation, bool $force = false)
    {
        $this->scheduleTransportation = $scheduleTransportation;
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
        (bool) $result = $this->softDelete ? $this->scheduleTransportation->delete() : $this->scheduleTransportation->forceDelete();

        event(new ScheduleDeleted($this->scheduleTransportation));

        return $result;
    }
}
