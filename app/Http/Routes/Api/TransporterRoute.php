<?php

namespace App\Http\Routes\Api;

use App\Http\Controllers\Api\TransporterController;
use Jalameta\Router\BaseRoute;

class TransporterRoute extends BaseRoute
{
    /**
     * Route path prefix.
     *
     * @var string
     */
    protected $prefix = '/transporter';

    /**
     * Registered route name.
     *
     * @var string
     */
    protected $name = 'api.transporter';

    /**
     * Register routes handled by this class.
     *
     * @return void
     */
    public function register()
    {
        $this->router->get($this->prefix(),[
            'as' => $this->name('list'),
            'uses' => $this->uses('list'),
        ]);
    }

    /**
     * Controller used by this route.
     *
     * @return string
     */
    public function controller()
    {
        return TransporterController::class;
    }
}
