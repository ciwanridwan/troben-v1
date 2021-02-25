<?php

namespace App\Events\Handlings;

use App\Models\Handling;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class HandlingModified
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Handling instance.
     *
     * @var \App\Models\Handling
     */
    public Handling $handling;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Handling $handling)
    {
        $this->handling = $handling;
    }
}
