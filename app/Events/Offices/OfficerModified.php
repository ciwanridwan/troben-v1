<?php

namespace App\Events\Offices;

use App\Models\Offices\Office;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OfficerModified
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Customer instance.
     *
     * @var Office
     */
    public Office $office;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Office $office)
    {
        $this->office = $office;
    }
}
