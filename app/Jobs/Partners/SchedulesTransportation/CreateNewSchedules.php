<?php

namespace App\Jobs\Partners\SchedulesTransportation;

use App\Events\Partners\SchedulesTransportation\NewScheduleCreated;
use App\Models\Partners\ScheduleTransportation;
use Illuminate\Bus\Batchable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CreateNewSchedules
{
    use Dispatchable, SerializesModels, InteractsWithQueue, Batchable;

    /**
     * @var ScheduleTransportation
     */
    public ScheduleTransportation $scheduleTransportation;

    /**
     * Filtered attributes.
     *
     * @var array
     */
    protected array $attributes;

    /**
     * CreateNewCustomer constructor.
     *
     * @param array $inputs
     *
     * @throws ValidationException
     */
    public function __construct(array $inputs = [])
    {
        $this->scheduleTransportation = new ScheduleTransportation();
        $this->attributes = Validator::make($inputs, [
            'partner_id' => ['required'],
            'origin_regency_id' => ['required'],
            'destination_regency_id' => ['required'],
            'departed_at' => ['required'],
        ])->validate();
    }

    /**
     * Handle the job.
     *
     * @return bool
     */
    public function handle(): bool
    {
        $this->scheduleTransportation->fill($this->attributes);

        if ($this->scheduleTransportation->save()) {
            event(new NewScheduleCreated($this->scheduleTransportation));
        }

        return $this->scheduleTransportation->exists;
    }
}
