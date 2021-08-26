<?php

namespace App\Events\Partners\Balance;

use App\Models\Partners\Balance\History;
use Illuminate\Foundation\Events\Dispatchable;

class NewHistoryCreated
{
    use Dispatchable;

    /**
     * History instance.
     *
     * @var History $history
     */
    public History $history;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(History $history)
    {
        $this->history = $history;
    }
}
