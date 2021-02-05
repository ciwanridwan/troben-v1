<?php

namespace App\Http\Routes\Api;

use App\Http\Controllers\Api\AccountController;
use Jalameta\Router\BaseRoute;

class AccountRoute extends BaseRoute
{
    /**
     * route prefix
     *
     * @var string
     */
    protected $prefix = "/me";

    /**
     * route name
     *
     * @var string
     */
    protected $name = "api.me";

    /**
     * Register routes handled by this class.
     *
     * @return void
     */
    public function register()
    {
        $this->router->get($this->prefix(), [
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
        return AccountController::class;
    }
}
