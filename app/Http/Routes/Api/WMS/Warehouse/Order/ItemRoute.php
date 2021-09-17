<?php

namespace App\Http\Routes\Api\WMS\Warehouse\Order;

use Jalameta\Router\BaseRoute;
use App\Http\Controllers\Api\WMS\Warehouse\Order\ItemController;

class ItemRoute extends BaseRoute
{
    protected $prefix = 'partner/warehouse/order/{package_hash}/item';

    protected $name = 'api.partner.warehouse.order.item';

    protected $middleware = [
        'partner.type:business,space,pool',
        'partner.role:warehouse',
        'partner.scope.role:warehouse',
    ];

    /**
     * Register routes handled by this class.
     *
     * @return void
     */
    public function register()
    {
        $this->router->post($this->prefix(''), [
            'as' => $this->name('store'),
            'uses' => $this->uses('store'),
        ]);

        $this->router->post($this->prefix('{item_hash}'), [
            'as' => $this->name('update'),
            'uses' => $this->uses('update'),
        ]);

        $this->router->delete($this->prefix('{item_hash}'), [
            'as' => $this->name('destroy'),
            'uses' => $this->uses('destroy'),
        ]);
    }

    /**
     * Controller used by this route.
     *
     * @return string
     */
    public function controller()
    {
        return ItemController::class;
    }
}
