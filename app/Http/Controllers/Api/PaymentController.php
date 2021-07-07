<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\Error;
use App\Http\Controllers\Controller;
use App\Http\Response;
use App\Models\Payments\Gateway;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentController extends Controller
{
    /**
     * @return JsonResponse
     * @throws \Throwable
     */
    public function index(): JsonResponse
    {
        return $this->jsonSuccess(JsonResource::make($this->getPaymentGateway()));
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     * @throws \Throwable
     */
    private function getPaymentGateway(): object
    {
        $gateway = Gateway::query()
            ->where('is_active', true)
            ->get([
                'id',
                'channel',
                'name',
                'is_fixed',
                'admin_charges'
            ]);

        throw_if(empty($gateway->toArray()), Error::make(Response::RC_UNAVAILABLE_PAYMENT_GATEWAY));

        return $gateway;
    }
}
