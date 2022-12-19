<?php

namespace App\Http\Routes\Api\Partner;

use App\Http\Controllers\Api\CorporateController;
use App\Http\Controllers\Api\Partner\PartnerController;
use Jalameta\Router\BaseRoute;

class PartnerRoute extends BaseRoute
{
    /**
     * Route path prefix.
     *
     * @var string
     */
    protected $prefix = '/partner';

    /**
     * Registered route name.
     *
     * @var string
     */
    protected $name = 'api.partner';

    /**
     * Register routes handled by this class.
     *
     * @return void
     */
    public function register()
    {
        $this->router->get($this->prefix('/list'), [
            'as' => $this->name('list'),
            'uses' => $this->uses('list'),
        ]);

        $this->router->get($this->prefix('/corporate'), [
            'as' => $this->name('corporate'),
            'uses' => $this->uses('partnerList', CorporateController::class),
        ]);

        $this->router->get($this->prefix('/corporate/customer'), [
            'as' => $this->name('corporate.customer'),
            'uses' => $this->uses('customerList', CorporateController::class),
        ]);

        $this->router->post($this->prefix('/corporate/order'), [
            'as' => $this->name('corporate.order'),
            'uses' => $this->uses('store', CorporateController::class),
        ]);

        $this->router->get($this->prefix('/corporate/order-list'), [
            'as' => $this->name('corporate.order-list'),
            'uses' => $this->uses('listOrder', CorporateController::class),
        ]);

        $this->router->get($this->prefix('/nearby'), [
            'as' => $this->name('nearby'),
            'uses' => $this->uses('nearby'),
        ]);

        $this->router->post($this->prefix('availability'), [
            'as' => $this->name('availability-set'),
            'uses' => $this->uses('availabilitySet'),
        ]);

        $this->router->get($this->prefix('availability'), [
            'as' => $this->name('availability-get'),
            'uses' => $this->uses('availabilityGet'),
        ]);
    }

    /**
     * Controller used by this route.
     *
     * @return string
     */
    public function controller()
    {
        return PartnerController::class;
    }
}
