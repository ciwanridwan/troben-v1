<?php

namespace App\Http\Routes\Api;

use Jalameta\Router\BaseRoute;
use App\Http\Controllers\Api\AccountController;

class AccountRoute extends BaseRoute
{
    /**
     * route prefix.
     *
     * @var string
     */
    protected $prefix = '/me';

    /**
     * route name.
     *
     * @var string
     */
    protected $name = 'api.me';

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

        $this->router->post($this->prefix(), [
            'as' => $this->name('update'),
            'uses' => $this->uses('update'),
        ]);

        $this->router->post($this->prefix('address/{customer_hash}'), [
            'as' => $this->name('storeAddress'),
            'uses' => $this->uses('storeAddress'),
        ]);

        $this->router->post($this->prefix('/password'), [
            'as' => $this->name('updatePassword'),
            'uses' => $this->uses('updatePassword'),
        ])->withoutMiddleware('api');
    }

    /**
     * Controller used by this route.
     *
     * @return string
     */
    public function controller(): string
    {
        return AccountController::class;
    }
}
