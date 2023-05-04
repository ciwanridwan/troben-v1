<?php

namespace App\Http\Routes\Api\Partner\Cashier;

use App\Http\Controllers\Api\Partner\Cashier\OrderController;
use Jalameta\Router\BaseRoute;

class OrderRoute extends BaseRoute
{
    protected $prefix = 'partner/cashier';

    protected $name = 'partner.cashier.order';

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
        $this->router->get($this->prefix('invoice'), [
            'as' => $this->name('invoice'),
            'uses' => $this->uses('index'),
        ]);

        $this->router->get($this->prefix('invoice/detail/{package_hash}'), [
            'as' => $this->name('invoice.detail'),
            'uses' => $this->uses('detail'),
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
