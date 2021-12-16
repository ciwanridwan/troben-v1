<?php

namespace App\Jobs\Partners\SchedulesTransportation;

use App\Events\Partners\SchedulesTransportation\ScheduleModificationFailed;
use App\Events\Partners\SchedulesTransportation\ScheduleModified;
use App\Models\Partners\ScheduleTransportation;
use Illuminate\Bus\Batchable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UpdateExistingSchedules
{
    use Dispatchable, SerializesModels, InteractsWithQueue, Batchable;

    /**
     * ScheduleTransportation instance.
     *
     * @var ScheduleTransportation
     */
    public ScheduleTransportation $scheduleTransportation;

    /**
     * Filtered attributes.
     *
     * @var array
     */
    public array $attributes;

    /**
     * UpdateExistingCustomer constructor.
     *
     * @param ScheduleTransportation $scheduleTransportation
     * @param $request
     * @throws ValidationException
     */
    public function __construct(ScheduleTransportation $scheduleTransportation, $request)
    {

        $this->attributes = Validator::make($request, [
            'origin_regency_id' => ['filled'],
            'destination_regency_id' => ['filled'],
            'departed_at' => ['filled'],
        ])->validate();

        $this->scheduleTransportation = $scheduleTransportation;
    }

    /**
     * Handle the job.
     *
     * @return bool
     */
    public function handle(): bool
    {
        collect($this->attributes)->each(fn ($v, $k) => $this->scheduleTransportation->{$k} = $v);
        if ($this->scheduleTransportation->isDirty()) {
            if ($this->scheduleTransportation->save()) {
                event(new ScheduleModified($this->scheduleTransportation));
            } else {
                event(new ScheduleModificationFailed($this->scheduleTransportation));
            }
        }

        return $this->scheduleTransportation->exists;
    }
}
