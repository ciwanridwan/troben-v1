<?php

namespace App\Http\Routes\Api\Payment;

use App\Http\Controllers\Api\Order\CancelController;
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
            ->withoutMiddleware(['api'])
            ->middleware('is.nicepay');

        $this->router->post($this->prefix('registration/dummy/{gateway_hash}/{package_hash}'), [
            'as' => $this->name('dummyRegistration'),
            'uses' => $this->uses('dummyRegistration')
        ]);

        $this->router->post($this->prefix('pay-for-cancel/dummy/{package_hash}/{gateway_hash}'), [
            'as' => $this->name('cancel.then.pay.dummy'),
            'uses' => $this->uses('payForCancelDummy', CancelController::class),
        ]);

        $this->router->post($this->prefix('pay-for-cancel/{package_hash}/{gateway_hash}'), [
            'as' => $this->name('cancel.then.pay'),
            'uses' => $this->uses('payForCancel', CancelController::class),
        ]);
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
