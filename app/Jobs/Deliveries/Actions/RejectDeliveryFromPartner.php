<?php

namespace App\Jobs\Deliveries\Actions;

use App\Events\Deliveries\Pickup\PackageRejectedByPartner;
use App\Models\Deliveries\Deliverable;
use App\Models\Deliveries\Delivery;
use App\Models\HistoryReject;
use App\Models\Packages\Package;
use App\Models\Partners\Partner;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Validation\ValidationException;

class RejectDeliveryFromPartner
{
    use Dispatchable;

    /**
     * @var Delivery
     */
    public Delivery $delivery;

    protected Partner $partner;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Delivery $delivery, Partner $partner)
    {
        $this->delivery = $delivery;
        $this->partner = $partner;
        $typeConditions = [Delivery::TYPE_PICKUP];
        throw_if(! in_array($this->delivery->type, $typeConditions), ValidationException::withMessages([
            'package' => __('Delivery should be in '.implode(',', $typeConditions).' Type'),
        ]));
        $statusConditions = [Delivery::STATUS_PENDING];
        throw_if(! in_array($this->delivery->status, $statusConditions), ValidationException::withMessages([
            'package' => __('Delivery should be in '.implode(',', $statusConditions).' Status'),
        ]));
        if ($this->delivery->partner->id !== $this->partner->id) {
            throw new \LogicException('chosen partner must had the delivery');
        }
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->delivery->packages->each(fn (Package $package) => $package->setAttribute('status', Package::STATUS_CREATED)->save());

        $history = new HistoryReject();
        $history->delivery_id = $this->delivery->id;

        $history->partner_id = $this->delivery->partner_id;
        $history->package_id = $this->delivery->packages[0]->id;
        $history->content = $this->delivery->code()->first()->content;
        $history->status = Delivery::STATUS_REJECTED;

        $history->save();
        $this->delivery->code()->delete();
        $this->delivery->delete();

        event(new PackageRejectedByPartner($this->delivery));

        return $this->delivery->exists;
    }
}
