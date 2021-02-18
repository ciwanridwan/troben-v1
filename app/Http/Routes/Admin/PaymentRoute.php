<?php

namespace App\Http\Routes\Admin;

use App\Http\Controllers\Admin\PaymentController;
use Jalameta\Router\BaseRoute;

class PaymentRoute extends BaseRoute
{

    /**
     * @var string
     */
    protected $prefix = "/payment";


    /**
     * @var string
     */
    protected $name = "admin.payment";

    /**
     * Register routes handled by this class.
     *
     * @return void
     */
    public function register()
    {

        $this->router->get($this->prefix, [
            'as' => $this->name,
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
        return PaymentController::class;
    }
}
