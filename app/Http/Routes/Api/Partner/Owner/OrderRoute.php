<?php

namespace App\Http\Routes\Api\Partner\Owner;

use Jalameta\Router\BaseRoute;
use App\Http\Controllers\Api\Partner\Owner\OrderController;
use App\Http\Controllers\Api\Partner\Warehouse\OrderController as WarehouseOrderController;

class OrderRoute extends BaseRoute
{
    protected $prefix = 'partner/owner/order';

    protected $name = 'api.partner.owner.order';

    protected $middleware = [
        'partner.role:owner',
        'partner.scope.role:owner',
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
        $this->router->get($this->prefix('code/{code_content}'), [
            'as' => $this->name('showByReceipt'),
            'uses' => $this->uses('showByReceipt', WarehouseOrderController::class),
        ]);
    }

    /**
     * Controller used by this route.
     *
     * @return string
     */
    public function controller()
    {
        return OrderController::class;
    }
}
