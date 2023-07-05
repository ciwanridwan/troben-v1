<?php

namespace App\Http\Controllers\Api\Payment;

use App\Actions\Payment\Nicepay\CheckPayment;
use App\Concerns\Nicepay\UsingNicepay;
use App\Events\Payment\Nicepay\PayByNicepay;
use App\Http\Controllers\Controller;
use App\Http\Resources\Payment\Nicepay\RegistrationResource;
use App\Http\Response;
use App\Models\Packages\Package;
use App\Models\Payments\Gateway;
use App\Models\Payments\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Concerns\Controllers\HasAdminCharge;
use App\Events\Payment\Nicepay\PayByNicePayDummy;
use App\Exceptions\PaymentHasPaidException;
use Carbon\Carbon;

class NicepayController extends Controller
{
    use UsingNicepay,  HasAdminCharge;

    protected Gateway $gateway;

    /**
     * @param Gateway $gateway
     * @param Package $package
     * @return JsonResponse
     * @throws \Throwable
     */
    public function registration(Gateway $gateway, Package $package): JsonResponse
    {
        throw_if($this->checkPaymentHasPaid($package), PaymentHasPaidException::make(Response::RC_PAYMENT_HAS_PAID));

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
	Log::debug('NicepayWebhook: ', $request->all());
        event(new PayByNicepay($request));

        return $this->jsonSuccess();
    }

    public function cancel(Package $package): JsonResponse
    {
        throw_if($this->checkPaymentHasPaid($package), PaymentHasPaidException::make(Response::RC_PAYMENT_HAS_PAID));

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

        if (is_null($payment)) {
            $msg = sprintf('Payment pending not found for: %s', $package->getKey());
            Log::error($msg, ['package' => $package, 'payments' => $package->payments()->get()]);
            throw new \Exception($msg);
        } else {
            $payment->setAttribute('status', Payment::STATUS_CANCELLED)->save();
        }

        if($package->status == Package::STATUS_WAITING_FOR_CANCEL_PAYMENT) {
            $package->status = Package::STATUS_CANCEL;
            $package->save();
        }
        if($package->status == Package::STATUS_WAITING_FOR_PAYMENT) {
            $package->status = Package::STATUS_ACCEPTED;
            $package->save();
        }
        return $this->jsonSuccess();
    }

    public function dummyRegistration(Gateway $gateway, Package $package): JsonResponse
    {
        $this->gateway = $gateway;
        $amt = 0;

        if ($package->multiDestination()->exists()) {
            $childId = $package->multiDestination()->get()->pluck('child_id')->toArray();
            $totalAmountChild = Package::whereIn('id', $childId)->get()->sum('total_amount');
            $totalAmount = $package->total_amount + $totalAmountChild;
            $amt = ceil($totalAmount + self::adminChargeCalculator($gateway, $totalAmount));

            $packageChildUpdate = Package::whereIn('id', $childId)->get();
            $packageChildUpdate->each(function ($q) {
                $q->status = Package::STATUS_WAITING_FOR_PACKING;
                $q->payment_status = Package::PAYMENT_STATUS_PAID;
                $q->save();
                event(new PayByNicePayDummy($q));
            });
        } else {
            $amt = ceil($package->total_amount + self::adminChargeCalculator($gateway, $package->total_amount));
        }

        $currentTime = Carbon::now();
        $expiredTime = $currentTime->addDays(7);

        $firstNum = 9999;
        $vaNumber = rand(100, 1000000000);

        $package->status = Package::STATUS_WAITING_FOR_PACKING;
        $package->payment_status = Package::PAYMENT_STATUS_PAID;
        $package->save();

        $data = [
            'total_amount' => $amt,
            'server_time' => $currentTime,
            'expired_time' => $expiredTime,
            'bank' => Gateway::convertChannel($this->gateway->channel)['bank'],
            'va_number' => $firstNum.$vaNumber,
        ];

        event(new PayByNicePayDummy($package));
        return (new Response(Response::RC_SUCCESS, $data))->json();
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
