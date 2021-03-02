<?php

namespace App\Events\Partners\Transporter;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class TransporterBulked
{
    use Dispatchable, InteractsWithSockets, SerializesModels;


    /**
     * Collection of transporter.
     *
     * @var \Illuminate\Support\Collection|\App\Models\Partners\Transporter[] $transporter
     */
    public Collection $transporter;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Collection $transporter)
    {
        $this->transporter = $transporter;
    }
}
