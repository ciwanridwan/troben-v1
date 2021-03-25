<?php

namespace App\Http\Routes\Api\Partner\Driver;

use Jalameta\Router\BaseRoute;
use App\Http\Controllers\Api\Partner\Driver\OrderController;

class OrderRoute extends BaseRoute
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
        $this->router->get($this->prefix, [
            'as' => $this->name,
            'uses' => $this->uses('index'),
        ]);

        // driver go to customer
        $this->router->patch($this->prefix('{package_hash}/accept'), [
            'as' => $this->name('accept'),
            'uses' => $this->uses('coming'),
        ]);

        // driver arrive at customer, load an items from customer
        $this->router->patch($this->prefix('{package_hash}/load'), [
            'as' => $this->name('load'),
            'uses' => $this->uses('coming'),
        ]);

        // driver go to partner (warehouse)
        $this->router->patch($this->prefix('{package_hash}/depart'), [
            'as' => $this->name('depart'),
            'uses' => $this->uses('coming'),
        ]);

        // driver arrive at partner (warehouse)
        $this->router->patch($this->prefix('{package_hash}/finish'), [
            'as' => $this->name('finish'),
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
