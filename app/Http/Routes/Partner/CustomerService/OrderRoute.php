<?php

namespace App\Http\Routes\Partner\CustomerService;

use Jalameta\Router\BaseRoute;
use App\Http\Controllers\Partner\CustomerService\OrderController;

class OrderRoute extends BaseRoute
{
    /**
     * @var string
     */
    protected $name = 'partner.customer_service.order';

    protected $prefix = '/partner/customer-service';

    /**
     * Register routes handled by this class.
     *
     * @return void
     */
    public function register()
    {
        $this->router->get($this->prefix, [
            'as' => $this->name('pickup'),
            'uses' => $this->uses('index'),
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
