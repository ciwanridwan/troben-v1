<?php

namespace App\Events\Partners\Balance;

use App\Models\Partners\Balance\DeliveryHistory;
use Illuminate\Foundation\Events\Dispatchable;

class NewDeliveryHistoryCreated
{
    use Dispatchable;

    /**
     * Delivery History instance.
     *
     * @var DeliveryHistory $history
     */
    public DeliveryHistory $history;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(DeliveryHistory $history)
    {
        $this->history = $history;
    }
}
