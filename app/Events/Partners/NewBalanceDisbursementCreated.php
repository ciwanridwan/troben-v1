<?php

namespace App\Events\Partners;

use App\Models\Payments\Withdrawal;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewBalanceDisbursementCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Partner instance.
     *
     * @var \App\Models\Partners\Partner
     */
    public Withdrawal $withdrawal;

    /**
     * Create a new event instance.
     *
     * @param \App\Models\Partners\Partner $partner
     */
    public function __construct(Withdrawal $withdrawal)
    {
        $this->$withdrawal = $withdrawal;
    }
}
