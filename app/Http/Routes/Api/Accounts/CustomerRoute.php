<?php

namespace App\Http\Routes\Api\Accounts;

use App\Http\Controllers\Api\Accounts\CustomerController;
use Jalameta\Router\BaseRoute;

class CustomerRoute extends BaseRoute
{
    protected $prefix = "/account/customer";
    protected $name = "api.account.customer";


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
        return CustomerController::class;
    }
}
