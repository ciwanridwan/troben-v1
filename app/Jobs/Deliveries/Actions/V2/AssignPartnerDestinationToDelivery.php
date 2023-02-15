<?php

namespace App\Jobs\Deliveries\Actions\V2;

use App\Models\Deliveries\Delivery;
use App\Models\Partners\Partner;
use App\Models\Partners\Pivot\UserablePivot;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Validation\ValidationException;

class AssignPartnerDestinationToDelivery
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

        if ($this->delivery->partner_id !== null) {
            throw new \LogicException('Delivery has added partner destination, cant assign again');
        }
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        throw_if(
            $this->delivery->status !== Delivery::STATUS_WAITING_ASSIGN_PARTNER,
            ValidationException::withMessages([
                'manifest' => __('no partner assigned to manifest.'),
            ])
        );

        $this->delivery->partner_id = $this->partner->id;
        $this->delivery->status = Delivery::STATUS_WAITING_ASSIGN_TRANSPORTER;
        $this->delivery->destination_regency_id = $this->partner->geo_regency_id;
        $this->delivery->destination_district_id = $this->partner->geo_district_id;
        $this->delivery->destination_sub_district_id = $this->partner->geo_sub_district_id;

        $this->delivery->partner()->associate($this->partner);
        $this->delivery->save();
    }
}
