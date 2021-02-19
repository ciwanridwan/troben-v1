<?php

namespace App\Http\Routes\Api\WebAdminResource\Master;

use App\Http\Controllers\Api\WebAdminResource\Master\CustomerController;
use Jalameta\Router\BaseRoute;

class CustomerRoute extends BaseRoute
{
    protected $prefix = "/admin/master/customer";
    protected $name = "api.admin.master.customer";


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
