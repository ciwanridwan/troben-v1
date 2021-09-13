<?php

namespace App\Http\Routes\Api\Kurir;

use App\Http\Controllers\Api\Kurir\OrderController;
use App\Http\Controllers\Api\Kurir\ManifestController as PartnerManifestController;
use Jalameta\Router\BaseRoute;

class OrderRoute extends BaseRoute
{
    protected $prefix = 'kurir/order';

    protected $name = 'api.kurir.order';

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
