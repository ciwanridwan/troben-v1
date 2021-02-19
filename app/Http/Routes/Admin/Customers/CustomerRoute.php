<?php

namespace App\Http\Routes\Admin\Customers;

use App\Http\Controllers\Admin\Customers\CustomerController;
use Jalameta\Router\BaseRoute;

class CustomerRoute extends BaseRoute
{
    /**
     * Define route name
     *
     * @var string
     */
    protected $name = 'admin.customer';

    /**
     * Define route prefix
     *
     * @var string
     */
    protected $prefix = 'customer';

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
     * Define used controller
     *
     * @return string
     */
    public function controller()
    {
        return CustomerController::class;
    }
}
