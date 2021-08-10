<?php

namespace App\Http\Routes\Api\Payment;

use App\Http\Controllers\Api\Payment\NicepayController;
use Jalameta\Router\BaseRoute;

class NicepayRoute extends BaseRoute
{
    protected $prefix = 'payment/nicepay/';

    protected $name = 'api.payment.nicepay';

    /**
     * Register routes handled by this class.
     *
     * @return void
     */
    public function register()
    {
        $this->router->post($this->prefix('registration/{gateway_hash}/{package_hash}'), [
            'as' => $this->name('registration'),
            'uses' => $this->uses('registration')
        ]);

        $this->router->post($this->prefix('cancel/{package_hash}'), [
            'as' => $this->name('cancel'),
            'uses' => $this->uses('cancel')
        ]);

        $this->router->post($this->prefix('webhook'), [
            'as' => $this->name('webhook'),
            'uses' => $this->uses('webhook')
        ])
            ->withoutMiddleware(['api','auth:sanctum'])
            ->middleware('is.nicepay');
    }

    /**
     * Controller used by this route.
     *
     * @return string
     */
    public function controller()
    {
        return NicepayController::class;
    }
}
