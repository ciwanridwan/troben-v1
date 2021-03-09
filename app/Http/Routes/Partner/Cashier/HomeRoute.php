<?php

namespace App\Http\Routes\Partner\Cashier;

use App\Http\Controllers\Partner\Cashier\HomeController;
use Jalameta\Router\BaseRoute;

class HomeRoute extends BaseRoute
{
    /**
     * Register routes handled by this class.
     *
     * @return void
     */
    public function register()
    {
        // make an awesome route
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
