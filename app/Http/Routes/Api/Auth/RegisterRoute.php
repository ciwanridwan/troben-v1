<?php

namespace App\Http\Routes\Api\Auth;

use Jalameta\Router\BaseRoute;
use App\Http\Controllers\Api\Auth\RegisterController;

class RegisterRoute extends BaseRoute
{
    /**
     *
     * Registered Name.
     *
     */
    protected $path = '/auth/register';

    /**
     *
     * Registered Name.
     *
     */
    protected $name = 'api.auth.register';

    /**
     * Register routes handled by this class.
     *
     * @return void
     */
    public function register()
    {
        // make an awesome route
        $this->router->post($this->path, [
            'as' => $this->name,
            'uses' => $this->uses('store'),
        ]);
    }

    /**
     * Controller used by this route.
     *
     * @return string
     */
    public function controller()
    {
        return RegisterController::class;
    }
}
