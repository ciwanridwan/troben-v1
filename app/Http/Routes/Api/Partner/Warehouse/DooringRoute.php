<?php

namespace App\Http\Routes\Api\Partner\Warehouse;

use App\Http\Controllers\Api\Partner\Warehouse\DooringController;
use Jalameta\Router\BaseRoute;

class DooringRoute extends BaseRoute
{
    protected $prefix = 'partner/warehouse/dooring';

    protected $name = 'api.partner.warehouse.dooring';

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
