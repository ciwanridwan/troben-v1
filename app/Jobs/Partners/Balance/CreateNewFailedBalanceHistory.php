<?php

namespace App\Jobs\Partners\Balance;

use App\Events\Partners\Balance\NewFailedHistoryCreated;
use App\Models\Deliveries\Delivery;
use App\Models\Partners\Balance\FailedHistory;
use App\Models\Partners\Partner;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Validation\ValidationException;

class CreateNewFailedBalanceHistory implements ShouldQueue
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
    public Delivery $delivery;
    public Partner $partner;

    public string $type;
    public int $package_id;

    /**
     * Construct of create new partner balance failedhistory.
     *
     * @param array $inputs
     * @throws ValidationException
     */
    public function __construct(Delivery $delivery, Partner $partner , $package_id)
    {
        $this->failedhistory = new FailedHistory();
        $this->delivery = $delivery;
        $this->partner = $partner;
        $this->package_id = $package_id;
    }

    /**
     * Create new partner's balance failedhistory.
     *
     * @return bool
     */
    public function handle(): bool
    {
        if ($this->package_id == 0){
            $this->attributes['package_id'] = $this->package_id;
            $this->attributes['type'] = FailedHistory::TYPE_TRANSIT;
        }else{
            $this->attributes['type'] = FailedHistory::TYPE_DOORING;
        }
        $this->attributes['partner_id'] = $this->partner->id;
        $this->attributes['delivery_id'] = $this->delivery->id;
        $this->attributes['package_id'] = $this->package_id;
        $this->attributes['status'] = 1;
        $this->attributes['created_by'] = 0;
        $this->attributes['updated_by'] = 0;
        $this->failedhistory->fill($this->attributes);
        if ($this->failedhistory->save()) {
            event(new NewFailedHistoryCreated($this->failedhistory));
        }

        return $this->failedhistory->exists;
    }
}
