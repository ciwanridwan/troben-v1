<?php

namespace App\Http\Routes\Api\Partner;

use App\Http\Controllers\Api\Dashboard\Owner\PartnerController;
use App\Http\Controllers\Api\Dashboard\Owner\ProfileController;
use App\Http\Controllers\Api\Dashboard\Owner\SummaryController;
use Jalameta\Router\BaseRoute;

class DashboardOwnerRoute extends BaseRoute
{
    /**Declare prefix */
    protected $prefix = '/partner/dashboard/owner';
    // protected $prefixProfile = '/partner/dashboard/owner/profile';

    /** Declare name for show in routing */
    protected $name = 'partner.dashboard.owner';

    /**
     * Declare middlware
     */
    protected $middleware = [
        'partner.scope.role:owner',
    ];
    /**
     * Register routes handled by this class.
     *
     * @return void
     */
    public function register()
    {
        $this->router->get($this->prefix('profile'), [
            'as' => $this->name('info'),
            'uses' => $this->uses('info', ProfileController::class),
        ]);

        $this->router->post($this->prefix('profile/update'), [
            'as' => $this->name('update'),
            'uses' => $this->uses('update', ProfileController::class),
        ]);

        $this->router->get($this->prefix('income'), [
            'as' => $this->name('income'),
            'uses' => $this->uses('income'),
        ]);

        $this->router->get($this->prefix('item-into-warehouse'), [
            'as' => $this->name('itemIntoWarehouse'),
            'uses' => $this->uses('itemIntoWarehouse'),
        ]);

        $this->router->get($this->prefix('incoming-orders'), [
            'as' => $this->name('incomingOrders'),
            'uses' => $this->uses('incomingOrders'),
        ]);

        $this->router->get($this->prefix('summary/incoming-orders'), [
            'as' => $this->name('summary.incomingOrders'),
            'uses' => $this->uses('incomingOrders', SummaryController::class),
        ]);


        $this->router->get($this->prefix('summary/income'), [
            'as' => $this->name('summary.income'),
            'uses' => $this->uses('income', SummaryController::class),
        ]);


        $this->router->get($this->prefix('summary/item-into-warehouse'), [
            'as' => $this->name('summary.item-into-warehouse'),
            'uses' => $this->uses('itemIntoWarehouse', SummaryController::class),
        ]);
    }

    public function controller()
    {
        return PartnerController::class;
    }
}
