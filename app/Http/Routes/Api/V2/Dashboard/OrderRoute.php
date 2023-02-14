<?php

namespace App\Http\Routes\Api\V2\Dashboard;

use App\Http\Controllers\Api\Order\V2\DashboardController;
use Jalameta\Router\BaseRoute;

class OrderRoute extends BaseRoute
{
    protected $prefix = 'trawlpack/dashboard/order';

    protected $name = 'api.v2.dashboard.order';

    /**
     * Register routes handled by this class.
     *
     * @return void
     */
    public function register()
    {
        $this->router->get($this->prefix, [
            'as' => $this->name('index'),
            'uses' => $this->uses('index'),
        ])->middleware('partner.scope.role:customer-service');

        $this->router->get($this->prefix('detail/{package_hash}'), [
            'as' => $this->name('detail'),
            'uses' => $this->uses('detail'),
        ]);

        $this->router->get($this->prefix('list/drivers'), [
            'as' => $this->name('list.drivers'),
            'uses' => $this->uses('listDrivers'),
        ]);

        $this->router->patch($this->prefix('assign/{delivery_hash}/{userable_hash}/drivers'), [
            'as' => $this->name('assign.driver'),
            'uses' => $this->uses('orderAssignation'),
        ]);

        $this->router->post($this->prefix('/create'), [
            'as' => $this->name('store'),
            'uses' => $this->uses('store'),
        ]);

        $this->router->post($this->prefix('{package_hash}/update'), [
            'as' => $this->name('update'),
            'uses' => $this->uses('update'),
        ]);

        $this->router->post($this->prefix('price/estimation'), [
            'as' => $this->name('estimation.prices'),
            'uses' => $this->uses('estimationPrices'),
        ]);
    }

    public function controller()
    {
        return DashboardController::class;
    }
}
