<?php

namespace App\Http\Routes\Api\Kurir;

use App\Http\Controllers\Api\Kurir\ManifestController;
use App\Http\Controllers\Api\Kurir\OrderController;
use App\Http\Controllers\Api\Partner\ManifestController as PartnerManifestController;
use Jalameta\Router\BaseRoute;

class ManifestRoute extends BaseRoute
{
    protected $prefix = 'kurir/manifest';

    protected $name = 'api.kurir.manifest';

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
