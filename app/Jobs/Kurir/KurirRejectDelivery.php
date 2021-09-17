<?php

namespace App\Jobs\Kurir;

use App\Events\Deliveries\Pickup\PackageRejectedByPartner;
use App\Models\Deliveries\Delivery;
use App\Models\HistoryReject;
use App\Models\Packages\Package;
use App\Models\Partners\Partner;
use App\Models\Partners\Pivot\UserablePivot;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Validation\ValidationException;

class KurirRejectDelivery
{
    use Dispatchable;

    /**
     * @var Delivery
     */
    public Delivery $delivery;

    public HistoryReject $rejected;

    protected Partner $partner;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Delivery $delivery)
    {
        $this->delivery = $delivery;
        $typeConditions = [Delivery::TYPE_PICKUP];
        throw_if(! in_array($this->delivery->type, $typeConditions), ValidationException::withMessages([
            'package' => __('Delivery should be in '.implode(',', $typeConditions).' Type'),
        ]));
        $statusConditions = [Delivery::STATUS_PENDING];
        throw_if(! in_array($this->delivery->status, $statusConditions), ValidationException::withMessages([
            'package' => __('Delivery should be in '.implode(',', $statusConditions).' Status'),
        ]));
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
        $userable = UserablePivot::where('id', $this->delivery->userable_id)
            ->where('userable_type', '=', 'App\Models\Partners\Transporter')
            ->first();
        $history->user_id = $userable->user_id;
        $history->content = $this->delivery->code()->first()->content;
        $history->status = Delivery::STATUS_REJECTED;
        $history->save();

        $this->delivery->userable_id = null;
        $this->delivery->save();
        $this->rejected = $history;
        event(new PackageRejectedByPartner($this->delivery));

        return $this->delivery->exists;
    }
}
