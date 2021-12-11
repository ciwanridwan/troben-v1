<?php

namespace App\Http\Routes\Api\V;

use App\Http\Controllers\Api\V\OfficeController;
use Jalameta\Router\BaseRoute;

class OfficeRoute extends BaseRoute
{
    /**
     * route prefix.
     *
     * @var string
     */
    protected $prefix = '/office';

    /**
     * route name.
     *
     * @var string
     */
    protected $name = 'api.v.office';

    /**
     * Register routes handled by this class.
     *
     * @return void
     */
    public function register()
    {
        $this->router->get($this->prefix('me'), [
            'as' => $this->name('profile'),
            'uses' => $this->uses('profile'),
        ]);

        $this->router->get($this->prefix(), [
            'as' => $this->name('index'),
            'uses' => $this->uses('index'),
        ])->middleware(['role:super-admin']);

        $this->router->post($this->prefix(), [
            'as' => $this->name('store'),
            'uses' => $this->uses('store'),
        ])->middleware(['role:super-admin']);

        $this->router->post($this->prefix('destroy'), [
            'as' => $this->name('destroy'),
            'uses' => $this->uses('destroy'),
        ])->middleware(['role:super-admin']);

        $this->router->post($this->prefix('{office_hash}'), [
            'as' => $this->name('update'),
            'uses' => $this->uses('update'),
        ])->middleware(['role:super-admin']);
    }

    /**
     * Controller used by this route.
     *
     * @return string
     */
    public function controller()
    {
        return OfficeController::class;
    }
}
