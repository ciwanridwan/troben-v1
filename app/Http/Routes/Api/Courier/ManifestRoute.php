<?php

namespace App\Http\Routes\Api\Courier;

use App\Http\Controllers\Api\Courier\ManifestController;
use Jalameta\Router\BaseRoute;

class ManifestRoute extends BaseRoute
{
    protected $prefix = 'courier/manifest';

    protected $name = 'api.courier.manifest';

    protected $middleware = [
        'partner.type:business,transporter',
        'partner.role:driver',
        'partner.scope.role:driver',
    ];

    /**
     * Register routes handled by this class.
     *
     * @return void
     */
    public function register()
    {
        $this->router->get($this->prefix, [
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
        return ManifestController::class;
    }
}
