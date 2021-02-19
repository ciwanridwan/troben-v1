<?php

namespace App\Http\Routes\Admin\Master;

use App\Http\Controllers\Admin\Master\CustomerController;
use Jalameta\Router\BaseRoute;

class CustomerRoute extends BaseRoute
{
    protected $prefix = 'master/customer';

    protected $name = 'admin.master.customer';

    /**
     * Register routes handled by this class.
     *
     * @return void
     */
    public function register()
    {
        $this->router->get($this->prefix, [
            'as' => $this->name,
            'uses' => $this->uses('index'),
        ]);
    }

    public function controller()
    {
        return CustomerController::class;
    }
}
