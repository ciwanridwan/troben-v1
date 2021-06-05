<?php

namespace App\Http\Routes\Api\Partner\Warehouse;

use App\Http\Controllers\Api\Partner\ManifestController as PartnerManifestController;
use Jalameta\Router\BaseRoute;
use App\Http\Controllers\Api\Partner\Warehouse\Manifest;
use App\Http\Controllers\Api\Partner\Warehouse\ManifestController;

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

        $this->router->post($this->prefix, [
            'as' => $this->name('store'),
            'uses' => $this->uses('store'),
        ]);

        $this->router->get($this->prefix('{delivery_hash}'), [
            'as' => $this->name('show'),
            'uses' => $this->uses('show', PartnerManifestController::class),
        ]);

        $this->router->get($this->prefix('assignable/partner'), [
            'as' => $this->name('assignable.partner'),
            'uses' => $this->uses('partner', Manifest\AssignableController::class),
        ]);

        $this->router->get($this->prefix('assignable/driver'), [
            'as' => $this->name('assignable.driver'),
            'uses' => $this->uses('driver', Manifest\AssignableController::class),
        ]);

        $this->router->get($this->prefix('assignable/package'), [
            'as' => $this->name('assignable.package'),
            'uses' => $this->uses('package', Manifest\AssignableController::class),
        ]);

        $this->router->patch($this->prefix('assignation/{delivery_hash}/driver/{userable_hash}'), [
            'as' => $this->name('assignation.driver'),
            'uses' => $this->uses('driver', Manifest\AssignationController::class),
        ]);

        $this->router->patch($this->prefix('assignation/{delivery_hash}/package'), [
            'as' => $this->name('assignation.package'),
            'uses' => $this->uses('package', Manifest\AssignationController::class),
        ]);

        $this->router->patch($this->prefix('unload/{delivery_hash}/package'), [
            'as' => $this->name('unload'),
            'uses' => $this->uses('unload', Manifest\UnloadController::class),
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
