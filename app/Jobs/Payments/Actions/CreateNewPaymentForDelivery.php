<?php

namespace App\Jobs\Payments\Actions;

use App\Jobs\Payments\CreateNewPayment;
use App\Models\Deliveries\Delivery;
use App\Models\Payments\Gateway;
use App\Models\Payments\Payment;
use Illuminate\Foundation\Bus\Dispatchable;

class CreateNewPaymentForDelivery
{
    use Dispatchable;

    public Payment $payment;

    public Gateway $gateway;

    public Delivery $delivery;

    protected array $attributes;

    /**
     * @param Delivery $delivery
     * @param Gateway $gateway
     * @param array $inputs
     */
    public function __construct(Delivery $delivery, Gateway $gateway, $inputs = [])
    {
        $this->delivery = $delivery;
        $this->gateway = $gateway;
        $this->attributes = $inputs;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $job = new CreateNewPayment($this->delivery, $this->gateway, $this->attributes);
        dispatch_now($job);
        $this->payment = $job->payment;
        return $this->payment->exists;
    }
}
