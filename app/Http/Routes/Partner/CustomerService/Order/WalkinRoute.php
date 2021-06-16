<?php

namespace App\Http\Routes\Partner\CustomerService\Order;

use App\Http\Controllers\Partner\CustomerService\Order\WalkinController;
use Jalameta\Router\BaseRoute;

class WalkinRoute extends BaseRoute
{

    /**
     * @var string
     */
    protected $name = 'partner.customer_service.order.walkin';

    protected $prefix = 'walkin';

    /**
     * Register routes handled by this class.
     *
     * @return void
     */
    public function register()
    {
        $this->router->get($this->prefix, [
            'as' => $this->name('create'),
            'uses' => $this->uses('create')
        ]);
    }

    /**
     * Controller used by this route.
     *
     * @return string
     */
    public function controller()
    {
        return WalkinController::class;
    }
}
