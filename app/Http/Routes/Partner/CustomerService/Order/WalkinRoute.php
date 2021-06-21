<?php

namespace App\Http\Routes\Partner\CustomerService\Order;

use App\Http\Controllers\Api\GeoController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Partner\CustomerService\Order\WalkinController;
use Jalameta\Router\BaseRoute;

class WalkinRoute extends BaseRoute
{
    /**
     * @var string
     */
    protected $name = 'partner.customer_service.order.walkin';

    protected $prefix = 'walkin';

    /**
     * Register routes handled by this class.
     *
     * @return void
     */
    public function register()
    {
        $this->router->get($this->prefix('geo'), [
            'as' => $this->name('geo'),
            'uses' => $this->uses('index', GeoController::class)
        ]);
        $this->router->get($this->prefix('service'), [
            'as' => $this->name('service'),
            'uses' => $this->uses('list', ServiceController::class)
        ]);
        $this->router->get($this->prefix('customer'), [
            'as' => $this->name('customer'),
            'uses' => $this->uses('customer')
        ]);
        $this->router->post($this->prefix('calculate'), [
            'as' => $this->name('calculate'),
            'uses' => $this->uses('calculate')
        ]);
        $this->router->get($this->prefix, [
            'as' => $this->name('create'),
            'uses' => $this->uses('create')
        ]);
        $this->router->post($this->prefix, [
            'as' => $this->name('store'),
            'uses' => $this->uses('store')
        ]);
    }

    /**
     * Controller used by this route.
     *
     * @return string
     */
    public function controller()
    {
        return WalkinController::class;
    }
}
