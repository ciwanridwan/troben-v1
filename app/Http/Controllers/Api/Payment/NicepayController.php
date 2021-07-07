<?php

namespace App\Http\Controllers\Api\Payment;

use App\Actions\Payment\Nicepay\RegistrationPayment;
use App\Events\Payment\Nicepay\PayByNicepay;
use App\Http\Controllers\Controller;
use App\Http\Resources\Payment\Nicepay\RegistrationResource;
use App\Models\Packages\Package;
use App\Models\Payments\Gateway;
use App\Models\Payments\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NicepayController extends Controller
{
    /**
     * @param Gateway $gateway
     * @param Package $package
     * @return JsonResponse
     * @throws \Throwable
     */
    public function registration(Gateway $gateway, Package $package): JsonResponse
    {
        switch (Gateway::convertChannel($gateway->channel)['type']):
            case 'va':
//                (new CheckPayment($package))->inquiryPayment();
                /** @var Payment $payment */
                $payment = $package->payments->first();
                if ($payment->expired_at >= \Carbon\Carbon::now()) {
                    $resource = [
                        'total_amount' => $payment->total_payment,
                        'va_number' => $payment->sender_account,
                        'bank' => $payment->sender_bank,
                        'server_time' => \Carbon\Carbon::now(),
                        'expired_va' => $payment->expired_at,
                    ];
                } else {
                    $payment->setAttribute('status', Payment::STATUS_EXPIRED)->save();
                    $resource = $this->getVA($package, $gateway);
                }
        break;
        case 'qris':
                $resource = $this->getQris($package, $gateway);
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

    /**
     * @param Package $package
     * @param Gateway $gateway
     * @return array
     * @throws \Throwable
     */
    protected function getVA(Package $package, Gateway $gateway): array
    {
        return (new RegistrationPayment($package, $gateway))->vaRegistration();
    }

    /**
     * @param Package $package
     * @param Gateway $gateway
     * @return array
     * @throws \Throwable
     */
    protected function getQris(Package $package, Gateway $gateway): array
    {
        return (new RegistrationPayment($package, $gateway))->qrisRegistration();
    }
}
