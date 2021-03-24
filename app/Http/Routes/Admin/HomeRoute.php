<?php

namespace App\Http\Routes\Admin;

use Jalameta\Router\BaseRoute;
use App\Http\Controllers\Admin\HomeController;

class HomeRoute extends BaseRoute
{
    protected $name = 'admin.home';

    protected $prefix = 'home';

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

        $this->router->patch($this->prefix('{package_hash}/{partner_hash}/assign'), [
            'as' => $this->name('assign'),
            'uses' => $this->uses('orderAssignation'),
        ]);
    }

    /**
     * Controller used by this route.
     *
     * @return string
     */
    public function controller()
    {
        return HomeController::class;
    }
}
