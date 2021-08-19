<?php

namespace App\Http\Routes\Admin;

use App\Http\Controllers\Api\GeoController;
use Jalameta\Router\BaseRoute;

class GeoRoute extends BaseRoute
{
    /**
     * @var string
     */
    protected $prefix = 'geo';

    /**
     * @var string
     */
    protected $name = 'admin.geo';

    /**
     * Register routes handled by this class.
     *
     * @return void
     */
    public function register()
    {
        $this->router->get($this->prefix, [
            'as' => $this->name,
            'uses' => $this->uses('index', GeoController::class)
        ]);
    }
}
