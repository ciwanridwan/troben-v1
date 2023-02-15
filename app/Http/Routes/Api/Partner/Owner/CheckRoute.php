<?php

namespace App\Http\Routes\Api\Partner\Owner;

use App\Http\Controllers\Api\Partner\Owner\CheckController;
use App\Http\Controllers\Api\PricingController;
use Jalameta\Router\BaseRoute;

class CheckRoute extends BaseRoute
{
    /**
     * Route path prefix.
     *
     * @var string
     */
    protected $prefix = '/partner/owner/check';

    /**
     * Registered route name.
     *
     * @var string
     */
    protected $name = 'api.partner.owner';

    /**
     * Register routes handled by this class.
     *
     * @return void
     */
    public function register()
    {
        $this->router->get($this->prefix('/price'), [
            'as' => $this->name('check.price'),
            'uses' => $this->uses('tarif', PricingController::class),
        ]);

        $this->router->get($this->prefix('/receipt'), [
            'as' => $this->name('check.receipt'),
            'uses' => $this->uses('receipt'),
        ]);

        $this->router->get($this->prefix('/receipt/detail/{content}'), [
            'as' => $this->name('check.receipt.detail'),
            'uses' => $this->uses('detailReceipt'),
        ]);
    }

    public function controller()
    {
        return CheckController::class;
    }
}
