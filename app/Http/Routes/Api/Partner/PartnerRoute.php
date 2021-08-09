<?php

namespace App\Http\Routes\Api\Partner;

use App\Http\Controllers\Api\Partner\PartnerController;
use App\Http\Controllers\Api\TransporterController;
use Jalameta\Router\BaseRoute;

class PartnerRoute extends BaseRoute
{
    /**
     * Route path prefix.
     *
     * @var string
     */
    protected $prefix = '/partner';

    /**
     * Registered route name.
     *
     * @var string
     */
    protected $name = 'api.partner';

    /**
     * Register routes handled by this class.
     *
     * @return void
     */
    public function register()
    {
        $this->router->get($this->prefix('/list'), [
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
        return PartnerController::class;
    }
}
