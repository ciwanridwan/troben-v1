<?php

namespace App\Http\Routes\Api\Partner\Warehouse;

use Jalameta\Router\BaseRoute;
use App\Http\Controllers\Api\Partner\Warehouse\OrderController;

class OrderRoute extends BaseRoute
{
    protected $prefix = 'partner/warehouse/order';

    protected $name = 'api.partner.warehouse.order';

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
        $this->router->get($this->prefix, [
            'as' => $this->name,
            'uses' => $this->uses('coming'),
        ]);

        $this->router->post($this->prefix('{package_hash}/item'), [
            'as' => $this->name('item.store'),
            'uses' => $this->uses('coming'),
        ]);

        $this->router->put($this->prefix('{package_hash}/item/{item_hash}'), [
            'as' => $this->name('item.update'),
            'uses' => $this->uses('coming'),
        ]);

        $this->router->delete($this->prefix('{package_hash}/item/{item_hash}'), [
            'as' => $this->name('item.destroy'),
            'uses' => $this->uses('coming'),
        ]);

        $this->router->patch($this->prefix('{package_hash}/estimated'), [
            'as' => $this->name('estimated'),
            'uses' => $this->uses('coming'),
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
