<?php

namespace App\Listeners\Partners;

use App\Models\Partners\Balance\DeliveryHistory;
use App\Models\Partners\Balance\History;
use Illuminate\Database\Eloquent\Model;

class UpdatePartnerBalanceByEvent
{
    protected Model $history;

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(object $event)
    {
        /**LAST SCRIPT */
        // $this->history = $event->history;
        // if (!($this->history->type === History::TYPE_WITHDRAW
        //     && ($this->history->description === History::DESCRIPTION_WITHDRAW_SUCCESS ||
        //         $this->history->description === History::DESCRIPTION_WITHDRAW_CONFIRMED))) {
        //     $this
        //         ->history->partner->setAttribute('balance', $this->getUpdatedPartnerBalance())->save();
        // }
        /**END LAST */

        /** @var History|DeliveryHistory|Model history */
        /**TODO NEW SCRIPT */
        $this->history = $event->history;
        if (!($this->history->type === History::TYPE_WITHDRAW && ($this->history->description === History::DESCRIPTION_WITHDRAW_APPROVED))) {
            $this->history->partner->setAttribute('balance', $this->getUpdatedPartnerBalance())->save();
        }
        /**END TODO */
    }

    /**
     * Giving updated partner's balance.
     *
     * @return float
     */
    protected function getUpdatedPartnerBalance(): float
    {
        /**LAST SCRIPT */
        // return ($this->history->type === History::TYPE_DEPOSIT
        //     || ($this->history->type === History::TYPE_WITHDRAW
        //         && $this->history->description === History::DESCRIPTION_WITHDRAW_REJECT
        //     ))
        //     ? $this->history->partner->balance + $this->history->balance
        //     : $this->history->partner->balance - $this->history->balance;
        /**END LAST */

        /**TODO NEW SCRIPT */
        return ($this->history->type === History::TYPE_DEPOSIT
            || ($this->history->type === History::TYPE_WITHDRAW
                && $this->history->description === History::DESCRIPTION_WITHDRAW_REQUESTED
            ))
            ? $this->history->partner->balance
            : $this->history->partner->balance - $this->history->balance;
        /**END TODO */
    }
}
