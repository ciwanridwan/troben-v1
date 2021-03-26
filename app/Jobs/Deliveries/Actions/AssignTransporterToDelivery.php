<?php

namespace App\Jobs\Deliveries\Actions;

use App\Models\Packages\Package;
use App\Models\Deliveries\Delivery;
use App\Models\Partners\Transporter;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Events\Deliveries\TransporterAssigned;

class AssignTransporterToDelivery
{
    use Dispatchable;

    public Delivery $delivery;

    public Transporter $transporter;

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
                ->each(fn (Package $package) => $package
                    ->setAttribute('status', Package::STATUS_WAITING_FOR_PICKUP)
                    ->save());
        }

        event(new TransporterAssigned($this->transporter, $this->delivery));
    }
}
