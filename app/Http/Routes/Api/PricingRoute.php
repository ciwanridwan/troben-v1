<?php

namespace App\Http\Routes\Api;

use App\Http\Controllers\Api\CorporateController;
use App\Http\Controllers\Api\Order\MotorBikeController;
use App\Http\Controllers\Api\Order\OrderController;
use Jalameta\Router\BaseRoute;
use App\Http\Controllers\Api\PricingController;

class PricingRoute extends BaseRoute
{
    /**
     * @var string
     */
    protected $prefix = '/pricing';

    protected $name = 'api.pricing';

    /**
     * Register routes handled by this class.
     *
     * @return void
     */
    public function register()
    {
        $this->router->get($this->prefix(), [
            'as' => $this->name,
            'uses' => $this->uses('index'),
        ]);

        $this->router->post($this->prefix('calculator'), [
            'as' => $this->name('calculator'),
            'uses' => $this->uses('calculate'),
        ])->withoutMiddleware('api');

        $this->router->post($this->prefix('calculator/bulk'), [
            'as' => $this->name('calculator.bulk'),
            'uses' => $this->uses('calculateNew'),
        ])->withoutMiddleware('api');

        $this->router->post($this->prefix('location'), [
            'as' => $this->name('location'),
            'uses' => $this->uses('locationCheck'),
        ])->withoutMiddleware('api');

        $this->router->post($this->prefix('/calculator/motorbike'), [
            'as' => $this->name('calculator.motorbike'),
            'uses' => $this->uses('motorbikeCheck', MotorBikeController::class),
        ])->withoutMiddleware('api');

        $this->router->post($this->prefix('/calculator/corporate'), [
            'as' => $this->name('calculator.corporate'),
            'uses' => $this->uses('calculate', CorporateController::class),
        ]);

        $this->router->get($this->prefix('tarif'), [
            'as' => $this->name('tarif'),
            'uses' => $this->uses('tarif'),
        ])->withoutMiddleware('api');

        $this->router->get($this->prefix('ship/schedule'), [
            'as' => $this->name('shipSchedule'),
            'uses' => $this->uses('shipSchedule'),
        ])->withoutMiddleware('api');

        $this->router->get($this->prefix('delivery/method'), [
            'as' => $this->name('delivery.method'),
            'uses' => $this->uses('chooseDeliveryMethod', OrderController::class),
        ])->withoutMiddleware('api');
    }

    /**
     * Controller used by this route.
     *
     * @return string
     */
    public function controller()
    {
        return PricingController::class;
    }
}
