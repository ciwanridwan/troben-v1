<?php

namespace App\Events\Partners\Balance;

use App\Models\Partners\Balance\FailedHistory;
use Illuminate\Foundation\Events\Dispatchable;

class NewFailedHistoryCreated
{
    use Dispatchable;

    /**
     * History instance.
     *
     * @var FailedHistory $history
     */
    public FailedHistory $history;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(FailedHistory $history)
    {
        $this->history = $history;
    }
}
