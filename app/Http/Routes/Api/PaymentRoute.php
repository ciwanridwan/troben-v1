<?php

namespace App\Http\Routes\Api;

use App\Http\Controllers\Api\PaymentController;
use Jalameta\Router\BaseRoute;

class PaymentRoute extends BaseRoute
{
    protected $prefix = 'payment';

    protected $name = 'api.payment';

    /**
     * Register routes handled by this class.
     *
     * @return void
     */
    public function register()
    {
        $this->router->get($this->prefix('/{package_hash}'), [
            'as' => $this->name,
            'uses' => $this->uses('index')
        ]);
    }

    /**
     * Controller used by this route.
     *
     * @return string
     */
    public function controller()
    {
        return PaymentController::class;
    }
}
