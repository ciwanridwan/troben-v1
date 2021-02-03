<?php

namespace App\Http\Routes\Api;

use Jalameta\Router\BaseRoute;
use App\Http\Controllers\Api\AuthController;

class AuthRoute extends BaseRoute
{
    /**
     *
     * Registered Name.
     *
     */
    protected $prefix = '/auth';

    /**
     *
     * Registered Name.
     *
     */
    protected $name = 'api.auth';

    /**
     * Register routes handled by this class.
     *
     * @return void
     */
    public function register()
    {
        // make an awesome route
        $this->router->post($this->prefix('login'), [
            'as' => $this->name('login'),
            'uses' => $this->uses('login'),
        ])->withoutMiddleware('api');

        $this->router->post($this->prefix('register'), [
            'as' => $this->name('register'),
            'uses' => $this->uses('register'),
        ])->withoutMiddleware('api');
    }

    /**
     * Controller used by this route.
     *
     * @return string
     */
    public function controller()
    {
        return AuthController::class;
    }
}
