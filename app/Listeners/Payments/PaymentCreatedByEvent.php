<?php

namespace App\Listeners\Payments;

use App\Events\Deliveries\DeliveryCreated;
use App\Events\Deliveries\DriverAssigned;
use App\Jobs\Payments\Actions\CreateNewPaymentForDelivery;
use App\Models\Deliveries\Delivery;
use Illuminate\Foundation\Bus\DispatchesJobs;

class PaymentCreatedByEvent
{
    use DispatchesJobs;
    /**
     * @var array
     */
    protected array $attributes;
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        switch (true) {
            case $event instanceof DriverAssigned:
                /** @var Delivery $delivery */
                $delivery = $event->delivery;

                $job = new CreateNewPaymentForDelivery($delivery);
                $this->dispatch($job);
                break;

            default:
                # code...
                break;
        }
    }
}
