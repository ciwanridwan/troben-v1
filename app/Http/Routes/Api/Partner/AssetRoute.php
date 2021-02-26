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
    protected $middleware = ['isUsers'];

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
