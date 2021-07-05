<?php

namespace App\Listeners\Payments;

use App\Events\Deliveries\DeliveryCreated;
use App\Events\Payment\Nicepay\Registration;
use App\Jobs\Payments\Actions\CreateNewPaymentForDelivery;
use App\Jobs\Payments\Actions\CreateNewPaymentForPackage;
use App\Models\Deliveries\Delivery;
use App\Models\Packages\Package;
use App\Models\Payments\Gateway;
use App\Models\Payments\Payment;
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
            case $event instanceof DeliveryCreated:
                /** @var Delivery $delivery */
                $delivery = $event->delivery;

                $job = new CreateNewPaymentForDelivery($delivery);
                $this->dispatch($job);
                break;
            case $event instanceof Registration\NewVacctRegistration:
                /** @var Package $package */
                $package = $event->package;

                $response = $event->response;

                /** @var Gateway $gateway */
                $gateway = $event->gateway;

                $jobs = new CreateNewPaymentForPackage($package, $gateway, [
                    'service_type' => Payment::SERVICE_TYPE_PAYMENT,
                    'payment_amount' => $package->total_amount,
                    'payment_ref_id' => $response->tXid,
                    'expired_at' => date_format(date_create($response->vacctValidDt . $response->vacctValidTm), 'Y-m-d H:i:s')
                ]);
                $this->dispatch($jobs);
                break;
            default:
                # code...
                break;
        }
    }
}
