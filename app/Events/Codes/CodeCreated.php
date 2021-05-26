<?php

namespace App\Events\Codes;

use App\Models\Code;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CodeCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var Code
     */
    public Code $code;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Code $code)
    {
        $this->code = $code;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
