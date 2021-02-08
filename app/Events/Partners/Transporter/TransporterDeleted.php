<?php

namespace App\Events\Partners\Transporter;

use App\Models\Partners\Transporter;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TransporterDeleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Transporter instance.
     * 
     * @var \App\Models\Partners\Transporter
     */
    public Transporter $transporter;

    /**
     * @param \App\Models\Partners\Transporter $transporter
     */
    public function __construct(Transporter $transporter)
    {
        $this->transporter = $transporter;
    }
}
