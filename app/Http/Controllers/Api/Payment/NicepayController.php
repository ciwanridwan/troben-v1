<?php

namespace App\Http\Controllers\Api\Payment;

use App\Actions\Payment\Nicepay\CheckPayment;
use App\Actions\Payment\Nicepay\RegistrationPayment;
use App\Events\Payment\Nicepay\PayByNicepay;
use App\Http\Controllers\Controller;
use App\Http\Resources\Payment\Nicepay\RegistrationResource;
use App\Models\Packages\Package;
use App\Models\Payments\Gateway;
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
                $resource = (new CheckPayment($package, $gateway))->vaRegistration();
        break;
        case 'qris':
                $resource = (new RegistrationPayment($package, $gateway))->qrisRegistration();
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
}
