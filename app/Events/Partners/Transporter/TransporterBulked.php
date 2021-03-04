<?php

namespace App\Events\Partners\Transporter;

use Illuminate\Support\Collection;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

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
