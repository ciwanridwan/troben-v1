<?php

namespace App\Http\Routes\Api\Auth;

use Jalameta\Router\BaseRoute;
use App\Http\Controllers\Api\Auth\AuthController;

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
        $this->router->post($this->prefix('login'), [
            'as' => $this->name('login'),
            'uses' => $this->uses('login'),
        ])->withoutMiddleware('api');

        $this->router->post('/super/auth/login', [
            'as' => $this->name('super'),
            'uses' => $this->uses('super'),
        ])->withoutMiddleware('api');

        $this->router->post($this->prefix('register'), [
            'as' => $this->name('register'),
            'uses' => $this->uses('register'),
        ])->withoutMiddleware('api');

        $this->router->post($this->prefix('forgotbyphone'), [
            'as' => $this->name('forgotByPhone'),
            'uses' => $this->uses('forgotByPhone'),
        ])->withoutMiddleware('api');

        $this->router->post($this->prefix('forgotbyemail'), [
            'as' => $this->name('forgotByEmail'),
            'uses' => $this->uses('forgotByEmail'),
        ])->withoutMiddleware('api');

        $this->router->post($this->prefix('updatesocial'), [
            'as' => $this->name('updateSocial'),
            'uses' => $this->uses('updateSocial'),
        ]);
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
