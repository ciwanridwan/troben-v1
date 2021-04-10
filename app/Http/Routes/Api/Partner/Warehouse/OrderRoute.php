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
            'uses' => $this->uses('index'),
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

        $this->router->patch($this->prefix('{package_hash}/estimated'), [
            'as' => $this->name('estimated'),
            'uses' => $this->uses('estimated'),
        ]);

        $this->router->patch($this->prefix('{package_hash}/packaging'), [
            'as' => $this->name('packaging'),
            'uses' => $this->uses('packaging'),
        ]);

        $this->router->patch($this->prefix('{package_hash}/packaged'), [
            'as' => $this->name('packaged'),
            'uses' => $this->uses('packaged'),
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
