<?php

namespace App\Http\Controllers\Api\Payment;

use App\Actions\Payment\Nicepay\CheckPayment;
use App\Concerns\Nicepay\UsingNicepay;
use App\Events\Payment\Nicepay\PayByNicepay;
use App\Exceptions\Error;
use App\Http\Controllers\Controller;
use App\Http\Resources\Payment\Nicepay\RegistrationResource;
use App\Http\Response;
use App\Models\Packages\Package;
use App\Models\Payments\Gateway;
use App\Models\Payments\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NicepayController extends Controller
{
    use UsingNicepay;

    /**
     * @param Gateway $gateway
     * @param Package $package
     * @return JsonResponse
     * @throws \Throwable
     */
    public function registration(Gateway $gateway, Package $package): JsonResponse
    {
        throw_if($this->checkPaymentHasPaid($package), Error::make(Response::RC_PAYMENT_HAS_PAID));

        Log::debug('NicepayController: ', ['package_code' => $package->code->content, 'channel' => $gateway->channel]);
        switch (Gateway::convertChannel($gateway->channel)['type']):
            case 'va':
                $resource = (new CheckPayment($package, $gateway))->vaRegistration();
        break;
        case 'qris':
                $resource = (new CheckPayment($package, $gateway))->qrisRegistration();
        break;
        endswitch;

        return $this->jsonSuccess(new RegistrationResource($resource ?? []));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function webhook(Request $request): JsonResponse
    {
        event(new PayByNicepay($request));

        return $this->jsonSuccess();
    }

    public function cancel(Package $package): JsonResponse
    {
        throw_if($this->checkPaymentHasPaid($package), Error::make(Response::RC_PAYMENT_HAS_PAID));

        /** @var Payment $payment */
        $payment = $package->payments()
            ->where('status', Payment::STATUS_PENDING)
            ->latest()
            ->first();

//        $now = Carbon::now()->format('YmdHis');
//        $job = new Cancel([
//            'timeStamp' => $now,
//            'tXid' => $payment->payment_ref_id,
//            'iMid' => config('nicepay.imid'),
//            'payMethod' => config('nicepay.payment_method_code.va'),
//            'cancelType' => '1',
//            'cancelMsg' => 'Request Cancel',
//            'merchantToken' => $this->merchantToken($now,$payment->payment_ref_id,$payment->total_payment),
//            'preauthToken' => '',
//            'amt' => $payment->total_payment,
//            'cancelServerIp' => '127.0.0.1',
//            'cancelUserId' => 'admin',
//            'cancelUserIp' => '127.0.0.1',
//            'cancelUserInfo' => 'Test Cancel',
//            'cancelRetryCnt' => '3',
//            'referenceNo' =>  $package->code->content,
//            'worker' => ''
//        ]);
//        $this->dispatchNow($job);

        $payment->setAttribute('status', Payment::STATUS_CANCELLED)->save();

        return $this->jsonSuccess();
    }

    private function checkPaymentHasPaid(Package $package): bool
    {
        $payment = $package->payments()
            ->where('status', Payment::STATUS_SUCCESS)
            ->latest()
            ->first();

        return ! is_null($payment);
    }
}
