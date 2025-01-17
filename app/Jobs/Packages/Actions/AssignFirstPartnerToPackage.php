<?php

namespace App\Jobs\Packages\Actions;

use App\Models\Packages\Package;
use App\Models\Partners\Partner;
use App\Models\Deliveries\Delivery;
use App\Events\Packages\PartnerAssigned;
use App\Jobs\Deliveries\CreateNewDelivery;
use Illuminate\Foundation\Bus\Dispatchable;

class AssignFirstPartnerToPackage
{
    use Dispatchable;

    /**
     * @var \App\Models\Packages\Package
     */
    public Package $package;

    /**
     * @var \App\Models\Partners\Partner
     */
    public Partner $partner;

    /**
     * @var Delivery
     */
    public Delivery $delivery;

    public function __construct(Package $package, Partner $partner)
    {
        $this->package = $package;
        $this->partner = $partner;
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function handle(): void
    {
        if ($this->package->deliveries()->count() === 0) {
            // creating first delivery for initialize partner

            $job = new CreateNewDelivery([
                'type' => Delivery::TYPE_PICKUP,
            ], $this->partner);

            dispatch_now($job);

            $job->delivery->packages()->attach($this->package);

            $this->delivery = $job->delivery;

            $this->package->status = Package::STATUS_PENDING;
            $this->package->save();

            event(new PartnerAssigned($this->package, $this->partner));
            return;
        }

        /** @var Delivery $firstDelivery */
        $firstDelivery = $this->package->deliveries()->first();
        $firstDelivery->partner()->associate($this->partner);
        $firstDelivery->save();

        $this->updatePackageStatusToPending();

        $this->delivery = $firstDelivery;

        event(new PartnerAssigned($this->package, $this->partner));
    }

    public function updatePackageStatusToPending()
    {
        $this->package->status = Package::STATUS_PENDING;
        $this->package->save();
    }
}
