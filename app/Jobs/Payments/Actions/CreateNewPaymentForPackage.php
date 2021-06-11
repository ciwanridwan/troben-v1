<?php

namespace App\Jobs\Payments\Actions;

use App\Jobs\Payments\CreateNewPayment;
use App\Models\Packages\Package;
use App\Models\Payments\Gateway;
use App\Models\Payments\Payment;
use Illuminate\Foundation\Bus\Dispatchable;

class CreateNewPaymentForPackage
{
    use Dispatchable;

    public Payment $payment;

    public Gateway $gateway;

    public Package $package;

    protected array $attributes;

    /**
     * @param Package $package
     * @param Gateway $gateway
     * @param array $inputs
     */
    public function __construct(Package $package, Gateway $gateway, $inputs = [])
    {
        $this->package = $package;
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
        $job = new CreateNewPayment($this->package, $this->gateway, $this->attributes);
        dispatch_now($job);
        $this->payment = $job->payment;
        return $this->payment->exists;
    }
}
