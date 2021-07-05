<?php

namespace App\Jobs\Payments\Actions;

use App\Jobs\Payments\CreateNewPayment;
use App\Models\Deliveries\Delivery;
use App\Models\Partners\Transporter;
use App\Models\Payments\Gateway;
use App\Models\Payments\Payment;
use Illuminate\Foundation\Bus\Dispatchable;

class CreateNewPaymentForDelivery
{
    use Dispatchable;

    public Payment $payment;

    public Gateway $gateway;

    public Delivery $delivery;

    public Transporter $transporter;

    protected array $attributes;

    /**
     * @param Delivery $delivery
     * @param Gateway $gateway
     * @param array $inputs
     */
    public function __construct(Delivery $delivery, ?array $inputs = [])
    {
        $this->delivery = $delivery;
        $this->attributes = [
            'service_type' => Payment::SERVICE_TYPE_DEPOSIT,
            'payment_amount' => $inputs['payment_amount'] ?? $this->getDefaultPrice(),
            'sender_bank' => '',
            'sender_account' => '',
            'sender_name' => '',
        ];
        $this->gateway = new Gateway();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        switch ($this->delivery->type) {
            case Delivery::TYPE_PICKUP:
                $this->attributes['payment_amount'] = $this->getDefaultPrice();
                break;

            default:
                # code...
                break;
        }
        $job = new CreateNewPayment($this->delivery, $this->gateway, $this->attributes);
        dispatch_sync($job);
        $this->payment = $job->payment;
        return $this->payment->exists;
    }
    public function getDefaultPrice()
    {
        /** @var Transporter $transporter */
        $this->transporter = $this->delivery->transporter;
        $generalType = Transporter::getGeneralType($this->transporter->type);
        return Transporter::getAvailableTransporterPrices()[$generalType];
    }
}
