<?php

namespace App\Http\Routes\Api;

use Jalameta\Router\BaseRoute;
use App\Http\Controllers\Api\GeoController;

class GeoRoute extends BaseRoute
{
    /**
     * Registered route name.
     *
     * @var string
     */
    protected $name = 'api.geo';

    /**
     * Route path prefix.
     *
     * @var string
     */
    protected $prefix = '/geo';

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
        return GeoController::class;
    }
}
