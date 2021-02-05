<?php

namespace App\Events\Partners;

use App\Models\Partners\Partner;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class NewPartnerCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Partner instance.
     * 
     * @var \App\Models\Partners\Partner
     */
    public Partner $partner;

    /**
     * Create a new event instance.
     * 
     * @param \App\Models\Partners\Partner $partner
     */
    public function __construct(Partner $partner)
    {
        $this->partner = $partner;
    }
}
