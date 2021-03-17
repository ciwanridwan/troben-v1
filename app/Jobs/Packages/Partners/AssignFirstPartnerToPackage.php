<?php

namespace App\Jobs\Packages\Partners;

use App\Models\Packages\Package;
use App\Models\Partners\Partner;
use App\Models\Deliveries\Delivery;
use App\Jobs\Deliveries\CreateNewDelivery;

class AssignFirstPartnerToPackage
{
    /**
     * @var \App\Models\Packages\Package
     */
    public Package $package;

    /**
     * @var \App\Models\Partners\Partner
     */
    private Partner $partner;

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

            return;
        }

        /** @var Delivery $firstDelivery */
        $firstDelivery = $this->package->deliveries()->first();

        $firstDelivery->partner()->associate($this->partner);

        $firstDelivery->save();
    }
}
