<?php

namespace App\Http\Routes;

use App\Http\Controllers\DefaultController;
use Jalameta\Router\BaseRoute;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;

class FortifyRoute extends BaseRoute
{
    /**
     * Registered route name.
     *
     * @var string
     */
    protected $name = 'auth';

    /**
     * Route path prefix.
     *
     * @var string
     */
    protected $prefix = '/auth';

    /**
     * Register routes handled by this class.
     *
     * @return void
     */
    public function register()
    {
        $this->router->get($this->prefix('login'), [
            'as' => $this->name('login'),
            'uses' => $this->uses('create'),
        ])->middleware(['web', 'guest']);

        $this->router->post($this->prefix('login'), [
            'as' => $this->name('login.store'),
            'uses' => $this->uses('store'),
        ])->middleware(['web', 'guest']);

        $this->router->any($this->prefix('logout'), [
            'as' => $this->name('logout'),
            'uses' => $this->uses('destroy'),
        ])->middleware(['web']);

        $this->router->post($this->prefix('check'), [
            'as' => $this->name('change.check'),
            'uses' => $this->uses('checkUsername', DefaultController::class),
        ])->middleware(['web', 'guest']);

        $this->router->post($this->prefix('change'), [
            'as' => $this->name('change.submit'),
            'uses' => $this->uses('changePasswordGuest', DefaultController::class),
        ])->middleware(['web', 'guest']);

        $this->router->get($this->prefix(), function () {
            return redirect()->route('auth.login');
        })->name($this->name);
    }

    /**
     * Get controller namespace.
     *
     * @return string
     */
    public function controller()
    {
        return AuthenticatedSessionController::class;
    }
}
