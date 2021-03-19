<?php

namespace App\Http\Routes\Api\Partner;

use Jalameta\Router\BaseRoute;
use App\Http\Controllers\Api\Partner\AssetController;

class AssetRoute extends BaseRoute
{
    /**
     * route prefix.
     *
     * @var string
     */
    protected $prefix = '/partner/asset';

    /**
     * route name.
     *
     * @var string
     */
    protected $name = 'api.partner.asset';

    /**
     * Middleware used for this route.
     *
     * @var array
     */
    protected $middleware = ['isUser'];

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

        $this->router->post($this->prefix('/{type}'), [
            'as' => $this->name('store'),
            'uses' => $this->uses('store'),
        ]);

        $this->router->patch($this->prefix('/{type}/{hash}'), [
            'as' => $this->name('update'),
            'uses' => $this->uses('update'),
        ]);

        $this->router->delete($this->prefix('/{type}/{hash}'), [
            'as' => $this->name('delete'),
            'uses' => $this->uses('destroy'),
        ]);
    }

    /**
     * Controller used by this route.
     *
     * @return string
     */
    public function controller()
    {
        return AssetController::class;
    }
}
