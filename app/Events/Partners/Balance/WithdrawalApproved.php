<?php

namespace App\Events\Partners\Balance;

use App\Models\Payments\Withdrawal;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WithdrawalApproved
{
    use Dispatchable, SerializesModels;

    /**
     * instance of Withdrawal / partner balance disbursement.
     *
     * @var Withdrawal $withdrawal
     */
    public Withdrawal $withdrawal;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Withdrawal $withdrawal)
    {
        $this->withdrawal = $withdrawal;
    }
}
