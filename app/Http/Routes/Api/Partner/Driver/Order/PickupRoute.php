<?php

namespace App\Http\Routes\Api\Partner\Driver\Order;

use App\Http\Controllers\Api\Partner\Driver\Order\PickupController;
use Jalameta\Router\BaseRoute;

class PickupRoute extends BaseRoute
{
    protected $prefix = 'partner/driver/order';

    protected $name = 'api.partner.driver.order';

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

        // driver go to customer
        $this->router->patch($this->prefix('{delivery_hash}/arrived'), [
            'as' => $this->name('arrived'),
            'uses' => $this->uses('arrived'),
        ]);

        // driver load an items from customer
        $this->router->patch($this->prefix('{delivery_hash}/loaded'), [
            'as' => $this->name('loaded'),
            'uses' => $this->uses('loaded'),
        ]);

        // driver arrive at partner (warehouse)
        $this->router->patch($this->prefix('{delivery_hash}/finished'), [
            'as' => $this->name('finished'),
            'uses' => $this->uses('finished'),
        ]);

        $this->router->patch($this->prefix('{delivery_hash}/unloaded'), [
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
        return PickupController::class;
    }
}
