<?php

namespace App\Http\Routes\Api\WMS\Warehouse;

use App\Http\Controllers\Api\WMS\ManifestController;
use App\Http\Controllers\Api\WMS\ManifestController as PartnerManifestController;
use App\Http\Controllers\Api\WMS\Warehouse\Manifest;
use Jalameta\Router\BaseRoute;

class ManifestRoute extends BaseRoute
{
    protected $prefix = 'wms/warehouse/manifest';

    protected $name = 'api.wms.warehouse.manifest';

    protected $middleware = [
        'partner.type:business,space,pool,transporter',
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
            'uses' => $this->uses('driver', Manifest\AssignableController::class),
        ]);

        $this->router->patch($this->prefix('assignation/{delivery_hash}/package'), [
            'as' => $this->name('assignation.package'),
            'uses' => $this->uses('package', Manifest\AssignationController::class),
        ]);

        $this->router->patch($this->prefix('assignation/{delivery_hash}/partner'), [
            'as' => $this->name('assignation.request'),
            'uses' => $this->uses('requestPartner', Manifest\AssignationController::class),
        ]);

        $this->router->patch($this->prefix('assignation/{delivery_hash}/partner/{partner_hash}'), [
            'as' => $this->name('assignation.partner'),
            'uses' => $this->uses('partner', Manifest\AssignationController::class),
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