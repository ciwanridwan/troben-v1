<?php

namespace App\Listeners\Payments;

use App\Events\Payment\Nicepay\PayByNicepay;
use App\Exceptions\Error;
use App\Http\Response;
use App\Models\Payments\Gateway;
use App\Models\Payments\Payment;
use Carbon\Carbon;

class UpdatePaymentByEvent
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * @param object $event
     * @throws \Throwable
     */
    public function handle(object $event): void
    {
        switch (true) {
            case $event instanceof PayByNicepay:
                $params = $event->params;

                throw_if($params->status !== '0', Error::make(Response::RC_PAYMENT_NOT_PAID));
                if ($params->status === '0') {
                    Payment::query()
                        ->where('payment_ref_id', $params->tXid)
                        ->update([
                            'status' => Payment::STATUS_SUCCESS,
                            'sender_bank' => $params->payMethod === config('nicepay.payment_method_code.va')
                                ? Gateway::convertChannel(array_flip(config('nicepay.bank_code'))[$params->bankCd])['bank']
                                : Gateway::convertChannel(Gateway::CHANNEL_NICEPAY_SHPPEE_QRIS),
                            'sender_name' => $params->billingNm,
                            'sender_account' => $params->vacctNo ?? null,
                            'confirmed_at' => Carbon::now(),
                        ]);
                    $event->broadcastOn();
                }
                break;
        }
    }
}
