<?php

namespace App\Listeners\Partners;

use App\Models\Partners\Balance\History;

class UpdatePartnerBalanceByEvent
{
    protected History $history;

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(object $event)
    {
        $this->history = $event->history;
        $this->history->partner->setAttribute('balance', $this->getUpdatedPartnerBalance())->save();
    }

    /**
     * Giving updated partner's balance.
     *
     * @return float
     */
    protected function getUpdatedPartnerBalance(): float
    {
        return ($this->history->type === History::TYPE_DEPOSIT)
            ? $this->history->partner->balance + $this->history->balance
            : $this->history->partner->balance - $this->history->balance;
    }
}
