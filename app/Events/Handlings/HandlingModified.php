<?php

namespace App\Events\Handlings;

use App\Models\Handling;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

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
