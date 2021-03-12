<?php

namespace App\Http\Routes\Partner\Cashier;

use App\Http\Controllers\Partner\Cashier\HomeController;
use Jalameta\Router\BaseRoute;

class HomeRoute extends BaseRoute
{
    protected $prefix = "home";

    protected $name = "partner.cashier.home";

    /**
     * Register routes handled by this class.
     *
     * @return void
     */
    public function register()
    {
        $this->router->get($this->prefix, [
            'as' => $this->name('all'),
            'uses' => $this->uses('index')
        ]);
    }

    /**
     * Controller used by this route.
     *
     * @return string
     */
    public function controller()
    {
        return HomeController::class;
    }
}
