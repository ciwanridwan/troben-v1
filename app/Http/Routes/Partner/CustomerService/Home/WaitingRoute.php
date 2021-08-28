<?php

namespace App\Http\Routes\Partner\CustomerService\Home;

use App\Http\Controllers\Partner\CustomerService\Home\WaitingController;
use Jalameta\Router\BaseRoute;

class WaitingRoute extends BaseRoute
{
    /**
     * @var string
     */
    protected $prefix = 'home/waiting';

    /**
     * @var string
     */
    protected $name = 'partner.customer_service.home.waiting';

    /**
     * Register routes handled by this class.
     *
     * @return void
     */
    public function register()
    {
        $this->router->get($this->prefix('confirmation'), [
            'as' => $this->name('confirmation'),
            'uses' => $this->uses('confirmation'),
        ]);

        $this->router->get($this->prefix('payment'), [
            'as' => $this->name('payment'),
            'uses' => $this->uses('payment'),
        ]);
    }

    /**
     * Controller used by this route.
     *
     * @return string
     */
    public function controller()
    {
        return WaitingController::class;
    }
}
