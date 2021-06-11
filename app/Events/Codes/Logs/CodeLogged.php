<?php

namespace App\Events\Codes\Logs;

use App\Models\CodeLogable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CodeLogged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public CodeLogable $codeLogable;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(CodeLogable $codeLogable)
    {
        $this->codeLogable = $codeLogable;
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
