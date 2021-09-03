<?php

namespace App\Http\Routes\Partner\CustomerService\Home;

use App\Http\Controllers\Partner\CustomerService\Home\OrderController;
use Jalameta\Router\BaseRoute;

class OrderRoute extends BaseRoute
{
    protected $prefix = 'home/order';

    protected $name = 'partner.customer_service.home.order';

    /**
     * Register routes handled by this class.
     *
     * @return void
     */
    public function register()
    {
        $this->router->get($this->prefix('pickup'), [
            'as' => $this->name('pickup'),
            'uses' => $this->uses('pickup'),
        ]);

        $this->router->get($this->prefix('assignCourier'), [
            'as' => $this->name('assignCourier'),
            'uses' => $this->uses('assignCourier'),
        ]);

        $this->router->get($this->prefix('passed'), [
            'as' => $this->name('passed'),
            'uses' => $this->uses('passed'),
        ]);

        $this->router->get($this->prefix('taken'), [
            'as' => $this->name('taken'),
            'uses' => $this->uses('taken'),
        ]);

        $this->router->patch($this->prefix('{delivery_hash}/{userable_hash}/assign'), [
            'as' => $this->name('assign'),
            'uses' => $this->uses('orderAssignation'),
        ]);
        $this->router->patch($this->prefix('{delivery_hash}/reject'), [
            'as' => $this->name('reject'),
            'uses' => $this->uses('orderReject'),
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
