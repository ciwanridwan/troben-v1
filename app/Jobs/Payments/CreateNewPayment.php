<?php

namespace App\Jobs\Payments;

use App\Concerns\Controllers\HasAdminCharge;
use App\Models\Payments\Gateway;
use App\Models\Payments\Payment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CreateNewPayment
{
    use Dispatchable, HasAdminCharge;

    public Payment $payment;

    public Gateway $gateway;

    public Model $payableModel;

    protected array $attributes;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Model $payableModel, Gateway $gateway, $inputs = [])
    {
        $this->payableModel = $payableModel;
        $this->gateway = $gateway;
        Validator::validate($inputs, [
            'service_type' => ['required', Rule::in(Payment::getAvailableServices())],
            'payment_amount' => ['required', 'numeric'],
            'sender_bank' => ['nullable'],
            'sender_account' => ['required_if:sender_bank,true'],
            'sender_name' => ['required_if:sender_bank,true']
        ]);
        $this->attributes = $inputs;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $defaultPaymentAttributes = [
            'total_payment' => $this->attributes['payment_amount'],
            'status' => Payment::STATUS_PENDING //payment was created not paid by customer
        ];

        $defaultGatewayAttributes = [];
        if ($this->gateway->exists) {
            $defaultGatewayAttributes = [
                'gateway_id' => $this->gateway->id,
                'payment_admin_charges' => self::adminChargeCalculator($this->gateway,$this->attributes['payment_amount']),
            ];
            $defaultPaymentAttributes['total_payment'] += $defaultGatewayAttributes['payment_admin_charges'];
            $defaultPaymentAttributes = array_merge($defaultGatewayAttributes,$defaultPaymentAttributes);
        }

        $this->attributes = array_merge($this->attributes, $defaultPaymentAttributes, $defaultGatewayAttributes);

        $this->payment = $this->payableModel->payments()->create($this->attributes);
        return $this->payment->exists;
    }
}
