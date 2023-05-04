<?php

namespace App\Http\Routes\Api\Partner\Cashier;

use App\Http\Controllers\Api\Order\V2\DashboardController;
use App\Http\Controllers\Api\Partner\Cashier\OrderController;
use App\Http\Controllers\Partner\Cashier\HomeController;
use Jalameta\Router\BaseRoute;

class OrderRoute extends BaseRoute
{
    protected $prefix = 'partner/cashier/invoice';

    protected $name = 'partner.cashier.order.invoice';

    protected $middleware = [
        'partner.role:cashier,customer-service',
        'partner.scope.role:cashier,customer-service',
    ];
    /**
     * Register routes handled by this class.
     *
     * @return void
     */
    public function register()
    {
        $this->router->get($this->prefix, [
            'as' => $this->name,
            'uses' => $this->uses('index'),
        ]);

        $this->router->get($this->prefix('detail/{package_hash}'), [
            'as' => $this->name('detail'),
            'uses' => $this->uses('detail', DashboardController::class),
        ]);

        $this->router->patch($this->prefix('send/{package_hash}'), [
            'as' => $this->name('send'),
            'uses' => $this->uses('packageChecked', HomeController::class),
        ]);
    }

    /**
     * Controller which used for this route
     */
    public function controller()
    {
        return OrderController::class;
    }
}
