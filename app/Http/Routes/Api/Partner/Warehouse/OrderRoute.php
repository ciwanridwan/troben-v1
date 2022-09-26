<?php

namespace App\Http\Routes\Api\Partner\Warehouse;

use Jalameta\Router\BaseRoute;
use App\Http\Controllers\Api\Partner\Warehouse\OrderController;

class OrderRoute extends BaseRoute
{
    protected $prefix = 'partner/warehouse/order';

    protected $name = 'api.partner.warehouse.order';

    protected $middleware = [
        'partner.type:business,space,pool,ho-hs,ho-sales,pos',
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
            'uses' => $this->uses('index'),
        ]);

        $this->router->get($this->prefix('dashboard'), [
            'as' => $this->name('dashboard'),
            'uses' => $this->uses('dashboard'),
        ]);

        $this->router->get($this->prefix('code/{code_content}'), [
            'as' => $this->name('showByReceipt'),
            'uses' => $this->uses('showByReceipt'),
        ]);

        $this->router->get($this->prefix('{package_hash}'), [
            'as' => $this->name('show'),
            'uses' => $this->uses('show'),
        ]);

        $this->router->put($this->prefix('{package_hash}'), [
            'as' => $this->name('update'),
            'uses' => $this->uses('update'),
        ]);

        $this->router->patch($this->prefix('{package_hash}/estimating'), [
            'as' => $this->name('estimating'),
            'uses' => $this->uses('estimating'),
        ]);

        $this->router->post($this->prefix('{package_hash}/upload'), [
            'as' => $this->name('upload'),
            'uses' => $this->uses('upload'),
        ]);

        $this->router->patch($this->prefix('{package_hash}/estimated'), [
            'as' => $this->name('estimated'),
            'uses' => $this->uses('estimated'),
        ]);

        $this->router->patch($this->prefix('{package_hash}/packing'), [
            'as' => $this->name('packing'),
            'uses' => $this->uses('packing'),
        ]);

        $this->router->patch($this->prefix('{package_hash}/packed'), [
            'as' => $this->name('packed'),
            'uses' => $this->uses('packed'),
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
