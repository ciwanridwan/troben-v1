<?php

namespace App\Http\Controllers\Api\Payment;

use App\Actions\Payment\Nicepay\RegistrationPayment;
use App\Events\Payment\Nicepay\PayingByVA;
use App\Http\Controllers\Controller;
use App\Http\Resources\Payment\Nicepay\RegistrationResource;
use App\Models\Packages\Package;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NicepayController extends Controller
{
    public function registration(Request $request, $paymentMethod, Package $package): JsonResponse
    {
        switch ($paymentMethod):
            case 'va':
                $bankCd = $request->input('bank_code');
                return $this->jsonSuccess(new RegistrationResource($this->getVA($package, $bankCd)));
            case 'qris':
                return $this->jsonSuccess(new RegistrationResource($this->getQris($package)));
        endswitch;

        return $this->jsonSuccess();
    }

    public function webhook(Request $request, $paymentMethod): JsonResponse
    {
        if ($paymentMethod === 'va') {
            event(new PayingByVA($request));
        } else if ($paymentMethod === 'qris') {
            event();
        }

        return $this->jsonSuccess();

    }

    protected function getVA(Package $package, $bankCd)
    {
        return (new RegistrationPayment($package))->vaRegistration($bankCd);
    }

    protected function getQris(Package $package)
    {
        return (new RegistrationPayment($package))->qrisRegistration();
    }
}
