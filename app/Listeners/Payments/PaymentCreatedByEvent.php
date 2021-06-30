<?php

namespace App\Listeners\Payments;

use App\Events\Deliveries\DeliveryCreated;
use App\Jobs\Payments\Actions\CreateNewPaymentForDelivery;
use App\Jobs\Payments\CreateNewPayment;
use App\Models\Deliveries\Delivery;
use App\Models\Payments\Gateway;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\InteractsWithQueue;

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
            case $event instanceof DeliveryCreated:
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
