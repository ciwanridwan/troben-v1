<?php

namespace App\Jobs\Partners\Balance;

use App\Events\Partners\Balance\NewFailedHistoryCreated;
use App\Exceptions\Error;
use App\Http\Response;
use App\Models\Deliveries\Delivery;
use App\Models\Packages\Package;
use App\Models\Partners\Balance\FailedHistory;
use App\Models\Partners\Partner;
use Illuminate\Foundation\Bus\Dispatchable;

class CreateNewFailedBalanceHistory
{
    use Dispatchable;

    /**
     * Partner balance failedhistory attributes.
     *
     * @var array $attributes
     */
    public array $attributes;

    /**
     * Partner balance failedhistory instance.
     *
     * @var FailedHistory $failedhistory
     */
    public FailedHistory $failedhistory;

    /**
     * Delivery instance.
     *
     * @var Delivery $delivery
     */
    public Delivery $delivery;

    /**
     * Partner instance.
     *
     * @var Partner $partner
     */
    public Partner $partner;

    /**
     * Package instance when exist
     *
     * @var Package|null $package
     */
    public ?Package $package;

    /**
     * Construct of create new partner balance failedhistory.
     *
     * @param Delivery $delivery
     * @param Partner $partner
     * @param Package|null $package
     * @throws \Throwable
     */
    public function __construct(Delivery $delivery, Partner $partner, ?Package $package = null)
    {
        throw_if($delivery->type === Delivery::TYPE_DOORING && ! $package, new Error(Response::RC_BAD_REQUEST));

        $this->failedhistory = new FailedHistory();
        $this->delivery = $delivery;
        $this->partner = $partner;
        $this->package = $package;
    }

    /**
     * Create new partner's balance failedhistory.
     *
     * @return bool
     */
    public function handle(): bool
    {
        if (! $this->package){
            $this->attributes['type'] = FailedHistory::TYPE_TRANSIT;
        }else{
            $this->attributes['type'] = FailedHistory::TYPE_DOORING;
            $this->attributes['package_id'] = $this->package->id;
        }
        $this->attributes['partner_id'] = $this->partner->id;
        $this->attributes['delivery_id'] = $this->delivery->id;
        $this->failedhistory->fill($this->attributes);
        if ($this->failedhistory->save()) {
            event(new NewFailedHistoryCreated($this->failedhistory));
        }

        return $this->failedhistory->exists;
    }
}
