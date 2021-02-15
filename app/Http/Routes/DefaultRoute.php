<?php

namespace App\Http\Routes;

use Jalameta\Router\BaseRoute;
use App\Http\Controllers\DefaultController;

class DefaultRoute extends BaseRoute
{
    /**
     * Route path prefix.
     *
     * @var string
     */
    protected $prefix = '/';

    /**
     * Registered route name.
     *
     * @var string
     */
    protected $name = 'home';

    /**
     * Register routes handled by this class.
     *
     * @return void
     */
    public function register()
    {
        $this->router->get($this->prefix(), [
            'as' => $this->name,
            'uses' => $this->uses('index'),
        ]);

        $this->router->view('debug', 'antd::auth.login');
    }

    /**
     * Controller used by this route.
     *
     * @return string
     */
    public function controller()
    {
        return DefaultController::class;
    }
}
