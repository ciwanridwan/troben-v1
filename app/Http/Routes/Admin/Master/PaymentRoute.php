<?php

namespace App\Http\Routes\Admin\Master;

use Jalameta\Router\BaseRoute;
use App\Http\Controllers\Admin\Master\PaymentController;

class PaymentRoute extends BaseRoute
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
        return PaymentController::class;
    }
}
