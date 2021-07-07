<?php

namespace App\Listeners\Payments;

use App\Events\Payment\Nicepay\PayByNicepay;
use App\Exceptions\Error;
use App\Http\Response;
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
     * Handle the event.
     *
     * @param  object  $event
     * @return void
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
                            'confirmed_at' => Carbon::now(),
                        ]);
                }
                break;
        }
    }
}
