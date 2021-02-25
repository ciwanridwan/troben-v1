<?php

namespace App\Http\Routes\Admin\Master;

use App\Http\Controllers\Admin\Master\EmployeeController;
use Jalameta\Router\BaseRoute;

class EmployeeRoute extends BaseRoute
{
    /**
     * @var string
     */
    protected $name = "admin.master.employee";

    /**
     * @var string
     */
    protected $prefix = "/master/employee";

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
        $this->router->patch($this->prefix, [
            'as' => $this->name('update'),
            'uses' => $this->uses('update')
        ]);

        $this->router->delete($this->prefix("/{hash}"), [
            'as' => $this->name('delete'),
            'uses' => $this->uses('destroy')
        ]);
    }

    /**
     * Controller used by this route.
     *
     * @return string
     */
    public function controller()
    {
        return EmployeeController::class;
    }
}
