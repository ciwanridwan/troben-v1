<?php

namespace App\Events\Partner\Transporter;

use App\Models\Partners\Transporter;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class TransporterCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Transporter Instance.
     *
     * @var App\Models\Partners\Transporter
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
