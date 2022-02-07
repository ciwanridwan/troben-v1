<?php

namespace App\Http\Routes\Api\Partner\Driver;

use Jalameta\Router\BaseRoute;
use App\Http\Controllers\Api\Partner\Driver\OrderController;
use App\Http\Controllers\Api\Partner\ManifestController as PartnerManifestController;

class OrderRoute extends BaseRoute
{
    protected $prefix = 'partner/driver/order';

    protected $name = 'api.partner.driver.order';

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

        $this->router->get($this->prefix('{delivery_hash}'), [
            'as' => $this->name('show'),
            'uses' => $this->uses('show', PartnerManifestController::class),
        ]);

        $this->router->post($this->prefix('detaildeliveries'), [
            'as' => $this->name('detaildeliveries'),
            'uses' => $this->uses('detailDeliveries')
        ]);
    }

    /**
     * Controller used by this route.
     *
     * @return string
     */
    public function controller()
    {
        return OrderController::class;
    }
}
