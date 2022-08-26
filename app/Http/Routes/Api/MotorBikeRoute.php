<?php

namespace App\Http\Routes\Api;

use App\Http\Controllers\Api\Order\MotorBikeController;
use Jalameta\Router\BaseRoute;

class MotorBikeRoute extends BaseRoute
{
    protected $prefix = 'order/motorbike';

    protected $name = 'api.order.motorbike';
    /**
     * Register routes handled by this class.
     *
     * @return void
     */
    public function register()
    {
        $this->router->post($this->prefix('store'), [
            'as' => $this->name('store'),
            'uses' => $this->uses('store'),
        ]);
    }

    /**Define Controller */
    public function controller()
    {
        return MotorBikeController::class;
    }
}