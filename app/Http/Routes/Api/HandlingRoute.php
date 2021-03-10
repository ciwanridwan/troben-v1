<?php

namespace App\Http\Routes\Api;

use App\Http\Controllers\Api\HandlingController;
use Jalameta\Router\BaseRoute;

class HandlingRoute extends BaseRoute
{
    protected $prefix = 'handling';

    protected $name = 'api.handling';

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
        return HandlingController::class;
    }
}
