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
        'partner.type:business,space,pool,transporter,ho-hs,ho-sales,pos',
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

        $this->router->post($this->prefix('detaildeliveries'), [
            'as' => $this->name('detaildeliveries'),
            'uses' => $this->uses('detailDeliveries')
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

        $this->router->patch($this->prefix('assignation/{delivery_hash}/partner'), [
            'as' => $this->name('assignation.request'),
            'uses' => $this->uses('requestPartner', Manifest\AssignationController::class),
        ]);

        $this->router->patch($this->prefix('assignation/{delivery_hash}/partner/{partner_hash}'), [
            'as' => $this->name('assignation.partner'),
            'uses' => $this->uses('partner', Manifest\AssignationController::class),
        ]);

        $this->router->patch($this->prefix('assignation/{delivery_hash}/partner-destination/{partner_hash}'), [
            'as' => $this->name('assignation.partner.destination'),
            'uses' => $this->uses('partnerDestination', Manifest\AssignationController::class),
        ]);

        $this->router->get($this->prefix('assignable/{delivery_hash}/partner'), [
            'as' => $this->name('assignable.delivery.partner'),
            'uses' => $this->uses('partner', Manifest\AssignableController::class),
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
