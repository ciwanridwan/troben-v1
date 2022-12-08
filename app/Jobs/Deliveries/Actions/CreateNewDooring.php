<?php

namespace App\Jobs\Deliveries\Actions;

use App\Events\Deliveries\DeliveryDooringCreated;
use App\Jobs\Deliveries\CreateNewDelivery;
use App\Models\Deliveries\Delivery;
use App\Models\Partners\Partner;
use Illuminate\Foundation\Bus\Dispatchable;

class CreateNewDooring
{
    use Dispatchable;

    public Delivery $delivery;

    private Partner $originPartner;

    private array $attributes;

    /**
     * CreateNewDooring constructor.
     * @param Partner $originPartner
     */
    public function __construct(Partner $originPartner)
    {
        $this->originPartner = $originPartner;

        $this->attributes = [
            'type' => Delivery::TYPE_DOORING,
            'status' => Delivery::STATUS_WAITING_ASSIGN_PACKAGE,
        ];
    }

    /**
     * @return bool
     * @throws \Illuminate\Validation\ValidationException
     */
    public function handle(): bool
    {
        $job = new CreateNewDelivery($this->attributes, null, $this->originPartner);
        dispatch_now($job);
        $this->delivery = $job->delivery;

        event(new DeliveryDooringCreated($this->delivery));

        return $this->delivery->exists;
    }
}
