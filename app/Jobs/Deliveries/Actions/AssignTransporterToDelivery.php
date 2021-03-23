<?php

namespace App\Jobs\Deliveries\Actions;

use App\Events\Deliveries\TransporterAssigned;
use App\Models\Deliveries\Delivery;
use App\Models\Packages\Package;
use App\Models\Partners\Transporter;
use Illuminate\Foundation\Bus\Dispatchable;

class AssignTransporterToDelivery
{
    use Dispatchable;

    private Delivery $delivery;

    private Transporter $transporter;

    public function __construct(Delivery $delivery, Transporter $transporter)
    {
        $this->delivery = $delivery;
        $this->transporter = $transporter;
    }

    public function handle()
    {
        $this->delivery->transporter()->associate($this->transporter);
        $this->delivery->status = Delivery::STATUS_ACCEPTED;
        $this->delivery->save();

        if ($this->delivery->type === Delivery::TYPE_PICKUP) {
            $this->delivery->packages()->cursor()
                ->each(fn(Package $package) => $package->setAttribute('status', Package::STATUS_WAITING_FOR_PICKUP))
                ->each(fn(Package $package) => $package->save());
        }

        event(new TransporterAssigned($this->transporter, $this->delivery));
    }
}
