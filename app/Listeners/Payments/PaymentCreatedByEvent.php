<?php

namespace App\Listeners\Payments;

use App\Events\Deliveries\DriverAssigned;
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
            case $event instanceof Registration\NewVacctRegistration:
                /** @var Package $package */
                $package = $event->package;

                $response = $event->response;

                /** @var Gateway $gateway */
                $gateway = $event->gateway;

                $attributes = [
                    'payment_content' => $response->vacctNo,
                    'expired_at' => date_format(date_create($response->vacctValidDt . $response->vacctValidTm), 'Y-m-d H:i:s'),
                    'service_type' => Payment::SERVICE_TYPE_PAYMENT,
                    'payment_amount' => $package->total_amount,
                    'payment_ref_id' => $response->tXid,
                ];

                $jobs = new CreateNewPaymentForPackage($package, $gateway, $attributes);
                $this->dispatch($jobs);
                break;
            case $event instanceof Registration\NewQrisRegistration:
                /** @var Package $package */
                $package = $event->package;

                $response = $event->response;

                /** @var Gateway $gateway */
                $gateway = $event->gateway;

                $attributes = [
                    'payment_content' => $response->qrContent,
                    'expired_at' => date_format(date_create($response->paymentExpDt . $response->paymentExpTm), 'Y-m-d H:i:s'),
                    'service_type' => Payment::SERVICE_TYPE_PAYMENT,
                    'payment_amount' => $package->total_amount,
                    'payment_ref_id' => $response->tXid,
                ];

                $jobs = new CreateNewPaymentForPackage($package, $gateway, $attributes);
                $this->dispatch($jobs);
                break;
            default:
                # code...
                break;
        }
    }
}
