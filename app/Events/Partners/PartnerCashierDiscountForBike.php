<?php

namespace App\Events\Partners;

use App\Models\Packages\Package;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PartnerCashierDiscountForBike
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Package instance.
     *
     * @var \App\Models\Packages\Package
     */
    public Package $package;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Package $package)
    {
        $this->package = $package;
    }
}
