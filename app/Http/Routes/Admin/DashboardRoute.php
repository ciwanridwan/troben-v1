<?php

namespace App\Http\Routes\Admin;

use App\Http\Controllers\Admin\DashboardController;
use Jalameta\Router\BaseRoute;

class DashboardRoute extends BaseRoute
{
    protected $name = 'admin.dashboard';

    protected $prefix = 'dashboard';

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

    /**
     * Controller used by this route.
     *
     * @return string
     */
    public function controller()
    {
        return DashboardController::class;
    }
}
