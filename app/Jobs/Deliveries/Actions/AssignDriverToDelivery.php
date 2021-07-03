<?php

namespace App\Jobs\Deliveries\Actions;

use App\Models\Partners\Partner;
use App\Models\User;
use App\Models\Packages\Package;
use App\Models\Deliveries\Delivery;
use App\Models\Partners\Transporter;
use App\Events\Deliveries\DriverAssigned;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\Partners\Pivot\UserablePivot;

class AssignDriverToDelivery
{
    use Dispatchable;

    public Delivery $delivery;

    public Transporter $transporter;

    /**
     * @var \App\Models\User
     */
    public User $driver;

    /**
     * @var \App\Models\Partners\Pivot\UserablePivot
     */
    private UserablePivot $userablePivot;

    public function __construct(Delivery $delivery, UserablePivot $userablePivot)
    {
        $this->delivery = $delivery;
        $this->userablePivot = $userablePivot;

        if (!$userablePivot->userable instanceof Transporter) {
            throw new \LogicException('chosen userable must be one that morph ' . Transporter::class . ' model');
        }

        $this->transporter = $userablePivot->userable;
        $this->driver = $userablePivot->user;
    }

    public function handle()
    {
        $this->delivery->assigned_to()->associate($this->userablePivot);
        if ($this->delivery->driver->partners->first()->type === Partner::TYPE_TRANSPORTER) {
            $this->delivery->status = Delivery::STATUS_WAITING_TRANSPORTER;
        } else {
            $this->delivery->status = Delivery::STATUS_ACCEPTED;
        }
        $this->delivery->save();

        if ($this->delivery->type === Delivery::TYPE_PICKUP) {
            $this->delivery->packages()->cursor()
                ->each(fn (Package $package) => $package
                    ->setAttribute('status', Package::STATUS_WAITING_FOR_PICKUP)
                    ->save());
        }
        event(new DriverAssigned($this->delivery, $this->userablePivot));
    }
}
