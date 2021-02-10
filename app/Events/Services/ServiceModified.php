<?php

namespace App\Events\Services;

use App\Models\Service;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class ServiceModified
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
     * @return void
     */
    public function __construct(Service $service)
    {
        $this->service = $service;
    }
}
