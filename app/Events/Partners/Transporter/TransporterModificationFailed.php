<?php

namespace App\Events\Partners\Transporter;

use App\Models\Partners\Transporter;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

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
