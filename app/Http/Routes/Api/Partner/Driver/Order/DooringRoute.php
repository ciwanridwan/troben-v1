<?php

namespace App\Http\Routes\Api\Partner\Driver\Order;

use App\Http\Controllers\Api\Partner\Driver\Order\DooringController;
use Jalameta\Router\BaseRoute;

class DooringRoute extends BaseRoute
{
    protected $prefix = 'partner/driver/order/dooring/{delivery_hash}';

    protected $name = 'api.partner.driver.order.dooring';

    protected $middleware = [
        'partner.type:business,transporter',
        'partner.role:driver',
        'partner.scope.role:driver',
    ];

    /**
     * Register routes handled by this class.
     *
     * @return void
     */
    public function register()
    {
        $this->router->patch($this->prefix('arrived'), [
            'as' => $this->name('arrived'),
            'uses' => $this->uses('arrived'),
        ]);

        // driver load an items from customer
        $this->router->patch($this->prefix('loaded'), [
            'as' => $this->name('loaded'),
            'uses' => $this->uses('loaded'),
        ]);

        // driver arrive at partner (warehouse)
        $this->router->patch($this->prefix('finished'), [
            'as' => $this->name('finished'),
            'uses' => $this->uses('finished'),
        ]);

        $this->router->post($this->prefix('unloaded/{package_hash}'), [
            'as' => $this->name('unloaded'),
            'uses' => $this->uses('unloaded'),
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
