<?php

namespace App\Http\Routes\Api\Partner\Warehouse;

use App\Http\Controllers\Api\Partner\Warehouse\ManifestController;
use Jalameta\Router\BaseRoute;

class ManifestRoute extends BaseRoute
{
    protected $prefix = 'partner/warehouse/manifest';

    protected $name = 'api.partner.warehouse.manifest';

    protected $middleware = [
        'partner.type:business,space,pool',
        'partner.role:warehouse',
        'partner.scope.role:warehouse',
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

        $this->router->get($this->prefix('partner'), [
            'as' => $this->name('partner'),
            'use' => $this->uses('partner'),
        ]);

        $this->router->post($this->prefix('store'), [
            'as' => $this->name('store'),
            'uses' => $this->uses('store'),
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
