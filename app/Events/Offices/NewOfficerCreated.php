<?php

namespace App\Events\Offices;

use App\Models\Offices\Office;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewOfficerCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var Office
     */
    public Office $office;

    /**
     * NewAdminCreated constructor.
     * @param Office $office
     */
    public function __construct(Office $office)
    {
        $this->office = $office;
    }
}
