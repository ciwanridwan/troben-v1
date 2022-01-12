<?php

namespace App\Events\Partners\SchedulesTransportation;

use App\Models\Partners\ScheduleTransportation;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewScheduleCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Customer instance.
     *
     * @var ScheduleTransportation
     */
    public ScheduleTransportation $scheduleTransportation;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(ScheduleTransportation $scheduleTransportation)
    {
        $this->scheduleTransportation = $scheduleTransportation;
    }
}
