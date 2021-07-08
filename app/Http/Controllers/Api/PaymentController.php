<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\Error;
use App\Http\Controllers\Controller;
use App\Http\Response;
use App\Models\Packages\Package;
use App\Models\Payments\Gateway;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentController extends Controller
{
    /**
     * @var Package $package
     */
    protected Package $package;

    /**
     * @var array $response
     */
    protected array $response;

    /**
     * @param Package $package
     * @return JsonResponse
     * @throws \Throwable
     */
    public function index(Package $package): JsonResponse
    {
        $this->package = $package;
        $this->getPaymentGateway()->isSelectable();
        return $this->jsonSuccess(JsonResource::make($this->response));
    }

    /**
     * Method for set response property.
     */
    private function getPaymentGateway(): object
    {
        $this->response = Gateway::query()
            ->where('is_active', true)
            ->get([
                'id',
                'channel',
                'name',
                'is_fixed',
                'admin_charges'
            ])->toArray();

        throw_if(empty($this->response), Error::make(Response::RC_UNAVAILABLE_PAYMENT_GATEWAY));

        return $this;
    }

    /**
     * Giving status for able to choosed or not.
     */
    private function isSelectable()
    {
        $gatewayChoosed = $this->package->payments->first()->gateway->channel ?? null;
        foreach ($this->response as $key => $gateway) {
            $this->response[$key]['selectable'] = is_null($gatewayChoosed) ? true : ($gateway['channel'] === $gatewayChoosed ? true : false);
        }
    }
}
