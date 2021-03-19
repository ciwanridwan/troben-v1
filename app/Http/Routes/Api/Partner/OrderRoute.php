<?php

namespace App\Http\Routes\Api\Partner;

use App\Http\Controllers\Api\Partner\OrderController;
use Jalameta\Router\BaseRoute;

class OrderRoute extends BaseRoute
{
    protected $prefix = 'partner/order';

    protected $name = 'api.partner.order';

    protected $middleware = ['isUser', 'partner.role:*', 'partner.type:*'];

    /**
     * Register routes handled by this class.
     *
     * @return void
     */
    public function register()
    {
        $this->router->get($this->prefix, [
            'as' => $this->name('index'),
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
