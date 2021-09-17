<?php

namespace App\Http\Routes\Api\WMS\Warehouse;

use App\Http\Controllers\Api\WMS\Warehouse\DooringController;
use Jalameta\Router\BaseRoute;

class DooringRoute extends BaseRoute
{
    protected $prefix = 'wms/warehouse/dooring';

    protected $name = 'api.wms.warehouse.dooring';

    protected $middleware = [
        'partner.type:business,space,pool,transporter',
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
        $this->router->post($this->prefix, [
            'as' => $this->name('store'),
            'uses' => $this->uses('store'),
        ]);
    }

    /**
     * Controller used by this route.
     *
     * @return string
     */
    public function controller()
    {
        return DooringController::class;
    }
}
