<?php

namespace App\Events\Partners\Transporter;

use App\Models\Partners\Transporter;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class TransporterModificationFailed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Transporter instance.
     * 
     * @var Transporter
     */
    public Transporter $transporter;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Transporter $transporter)
    {
        $this->transporter = $transporter;
    }
}
