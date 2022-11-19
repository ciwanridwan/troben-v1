<?php

namespace App\Jobs\Packages\Actions;

use App\Events\Packages\PartnerAssigned;
use App\Jobs\Deliveries\CreateNewDelivery;
use App\Models\Deliveries\Delivery;
use App\Models\Packages\Package;
use App\Models\Partners\Partner;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Collection;

class MultiAssignFirstPartner
{
    use Dispatchable;

    /**
     * @var Collection
     */
    public Collection $packages;

    /**
     * @var \App\Models\Partners\Partner
     */
    public Partner $partner;

    /**
     * @var Delivery
     */
    public Delivery $delivery;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $inputs, Partner $partner)
    {
        $this->packages = Package::whereIn('id', array_column($inputs, 'package_id'))->get();
        $this->partner = $partner;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->packages->each(function ($q) {
            if ($q->deliveries()->count() === 0) {

                $job = new CreateNewDelivery([
                    'type' => Delivery::TYPE_PICKUP,
                ], $this->partner);

                dispatch_now($job);

                $job->delivery->packages()->attach($q);

                $this->delivery = $job->delivery;

                $q->status = Package::STATUS_PENDING;
                $q->save();

                event(new PartnerAssigned($q, $this->partner));
            }
        });

        $this->packages->each(function ($q) {
            /** @var Delivery $firstDelivery */
            $firstDelivery = $q->deliveries()->first();
            $firstDelivery->partner()->associate($this->partner);
            $firstDelivery->save();

            $this->updatePackageStatusToPending();

            $this->delivery = $firstDelivery;

            event(new PartnerAssigned($q, $this->partner));
        });
    }

    public function updatePackageStatusToPending()
    {
        $this->packages->each(function ($q) {
            $q->status = Package::STATUS_PENDING;
            $q->save();
        });
    }
}
