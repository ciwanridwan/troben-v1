<?php

namespace App\Http\Routes\Api\V2\Dashboard;

use App\Http\Controllers\Api\Order\V2\DashboardController;
use App\Http\Controllers\Partner\CustomerService\Home\OrderController;
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

        $this->router->post($this->prefix('assign/{delivery_hash}/{userable_hash}/drivers'), [
            'as' => $this->name('assign.driver'),
            'uses' => $this->uses('orderAssignation', OrderController::class),
        ]);
    }

    public function controller()
    {
        return DashboardController::class;
    }
}
