<?php

namespace App\Http\Routes\Api;

use App\Http\Controllers\Api\VersionController;
use Jalameta\Router\BaseRoute;

class VersionRoute extends BaseRoute
{
    /**
     *
     * Registered Name.
     *
     */
    protected $prefix = '/version';

    /**
     *
     * Registered Name.
     *
     */
    protected $name = 'api.version';

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
        return VersionController::class;
    }
}
