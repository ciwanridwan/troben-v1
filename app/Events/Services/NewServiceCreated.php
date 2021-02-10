<?php

namespace App\Events\Services;

use App\Models\Service;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewServiceCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Service instance.
     * 
     * @var \App\Models\Service
     */
    public Service $service;

    /**
     * Create a new event instance.
     *
     * @param \App\Models\Service $service
     */
    public function __construct(Service $service)
    {
        $this->service = $service;
    }
}
