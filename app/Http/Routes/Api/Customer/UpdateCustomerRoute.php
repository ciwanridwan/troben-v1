<?php

namespace App\Http\Routes\Api\Customer;

use App\Http\Controllers\Api\Customer\UpdateCustomerController;
use Jalameta\Router\BaseRoute;

class UpdateCustomerRoute extends BaseRoute
{
    protected $prefix = '/customer';
    protected $name = 'api.customer.update';
    /**
     * Register routes handled by this class.
     *
     * @return void
     */
    public function register()
    {
        $this->router->put($this->prefix(), [
            'as' => $this->name,
            'uses' => $this->uses('index'),
        ]);
    }

    public function controller()
    {
        return UpdateCustomerController::class;
    }
}