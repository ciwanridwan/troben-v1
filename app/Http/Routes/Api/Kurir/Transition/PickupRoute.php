<?php

namespace App\Http\Routes\Api\Kurir\Transition;

use App\Http\Controllers\Api\Kurir\Transition\PickupController;
use Jalameta\Router\BaseRoute;

class PickupRoute extends BaseRoute
{
    protected $prefix = 'kurir/transition/pickup/{delivery_hash}';

    protected $name = 'api.kurir.transition.pickup';

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

        // driver go to customer
        $this->router->patch($this->prefix('arrived'), [
            'as' => $this->name('arrived'),
            'uses' => $this->uses('arrived'),
        ]);

        // driver load an items from customer
        $this->router->patch($this->prefix('loaded'), [
            'as' => $this->name('loaded'),
            'uses' => $this->uses('loaded'),
        ]);

        // driver arrive at partner (warehouse)
        $this->router->patch($this->prefix('finished'), [
            'as' => $this->name('finished'),
            'uses' => $this->uses('finished'),
        ]);

        $this->router->patch($this->prefix('unloaded'), [
            'as' => $this->name('unloaded'),
            'uses' => $this->uses('unloaded'),
        ]);
    }

    /**
     * Controller used by this route.
     *
     * @return string
     */
    public function controller()
    {
        return PickupController::class;
    }
}
