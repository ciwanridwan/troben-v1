<?php

namespace App\Jobs\Packages\Actions;

use App\Events\Packages\PartnerAssigned;
use App\Jobs\Deliveries\CreateNewDeliveryMultipleOrder;
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
    public function __construct(array $inputs, Partner $partner, string $type)
    {
        if ($type === 'new') {
            $this->packages = Package::whereIn('id', $inputs['id'])->get();
        } else {
            $this->packages = Package::whereIn('id', array_column($inputs, 'id'))->get();
        }

        $this->partner = $partner;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $job = new CreateNewDeliveryMultipleOrder([
            'type' => Delivery::TYPE_PICKUP,
        ], $this->partner);

        dispatch_now($job);

        $this->delivery = $job->delivery;

        $this->delivery->packages()->attach($this->packages->each(function ($q) {
            $q->status = Package::STATUS_PENDING;
            $q->save();

            event(new PartnerAssigned($q, $this->partner));
            return $q;
        }));
    }
}
