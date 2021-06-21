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
