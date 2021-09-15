<?php

namespace App\Http\Routes\Api\Courier;

use App\Http\Controllers\Api\Courier\OrderController;
use App\Http\Controllers\Api\Courier\ManifestController as PartnerManifestController;
use Jalameta\Router\BaseRoute;

class OrderRoute extends BaseRoute
{
    protected $prefix = 'courier/order';

    protected $name = 'api.courier.order';

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
        $this->router->patch($this->prefix('{delivery_hash}/reject'), [
            'as' => $this->name('reject'),
            'uses' => $this->uses('reject'),
        ]);

        $this->router->patch($this->prefix('{delivery_hash}/accept'), [
            'as' => $this->name('accept'),
            'uses' => $this->uses('accept'),
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
