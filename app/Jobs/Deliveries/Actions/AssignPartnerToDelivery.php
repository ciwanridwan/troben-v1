<?php

namespace App\Jobs\Deliveries\Actions;

use App\Models\Deliveries\Delivery;
use App\Events\Deliveries\PartnerAssigned;
use App\Models\Partners\Partner;
use App\Models\Partners\Pivot\UserablePivot;
use Illuminate\Foundation\Bus\Dispatchable;

/**
 * Class AssignPartnerToDelivery
 * @package App\Jobs\Deliveries\Actions
 */
class AssignPartnerToDelivery
{
    use Dispatchable;

    /**
     * @var Delivery $delivery
     */
    public Delivery $delivery;

    /**
     * @var Partner $partner
     */
    public Partner $partner;

    /**
     * @var UserablePivot $userablePivot
     */
    private UserablePivot $userablePivot;

    /**
     * AssignPartnerToDelivery constructor.
     * @param Delivery $delivery
     * @param Partner $partner
     */
    public function __construct(Delivery $delivery, Partner $partner)
    {
        $this->delivery = $delivery;
        $this->partner = $partner;
        $this->userablePivot = $partner->owner()->first()->pivot;

        if (! $this->userablePivot->userable instanceof Partner) {
            throw new \LogicException('chosen userable must be one that morph '.Partner::class.' model');
        }
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->delivery->assigned_to()->associate($this->userablePivot);
        $this->delivery->status = Delivery::STATUS_WAITING_PARTNER_ASSIGN_TRANSPORTER;
        $this->delivery->save();

        event(new PartnerAssigned($this->delivery, $this->partner));
    }
}
