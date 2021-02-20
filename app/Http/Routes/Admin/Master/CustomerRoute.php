<?php

namespace App\Http\Routes\Admin\Master;

use Jalameta\Router\BaseRoute;
use App\Http\Controllers\Admin\Master\CustomerController;

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

        $this->router->delete($this->prefix('{hash}'), [
            'as' => $this->name('delete'),
            'uses' => $this->uses('destroy'),
        ]);
    }

    public function controller()
    {
        return CustomerController::class;
    }
}
