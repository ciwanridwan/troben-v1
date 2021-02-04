<?php

namespace App\Events\Partners;

use App\Models\Partners\Partner;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PartnerModificationFailed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Partner instance.
     * 
     * @var App\Models\Partners\Partner
     */
    public Partner $partner;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Partner $partner)
    {
        $this->partner = $partner;
    }
}
