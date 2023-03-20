<?php

namespace App\Http\Routes\Api\Partner;

use App\Http\Controllers\Api\Dashboard\Owner\PartnerController;
use App\Http\Controllers\Api\Dashboard\Owner\ProfileController;
use Jalameta\Router\BaseRoute;

class DashboardOwnerRoute extends BaseRoute
{
    /**Declare prefix */
    protected $prefix = '/partner/dashboard/owner';
    // protected $prefixProfile = '/partner/dashboard/owner/profile';

    /** Declare name for show in routing */
    protected $name = 'partner.dashboard.owner';
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

        $this->router->post($this->prefix('item-into-warehouse'), [
            'as' => $this->name('update'),
            'uses' => $this->uses('update'),
        ]);
    }

    public function controller()
    {
        return PartnerController::class;
    }
}