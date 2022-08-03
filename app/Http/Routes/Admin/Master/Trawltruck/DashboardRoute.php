<?php

namespace App\Http\Routes\Admin\Master\Trawltruck;

use App\Http\Controllers\Admin\Trawltruck\DashboardController;
use Jalameta\Router\BaseRoute;

class DashboardRoute extends BaseRoute
{
    /**Declare prefix */
    protected $prefix = '/master/trawltruck/dashboard';

    /** Declare name for show in routing */
    protected $name = 'admin.master.trawltruck.dashboard';

    /**
     * Register routes handled by this class.
     *
     * @return void
     */
    public function register()
    {
        $this->router->get($this->prefix('driver/register'), [
            'as' => $this->name('driver.register'),
            'uses' => $this->uses('registerDriver'),
        ]);

        $this->router->get($this->prefix('driver/account'), [
            'as' => $this->name('driver.account'),
            'uses' => $this->uses('accountDriver'),
        ]);

        $this->router->get($this->prefix('driver/suspend'), [
            'as' => $this->name('driver.suspend'),
            'uses' => $this->uses('suspendDriver'),
        ]);

        $this->router->get($this->prefix('tracking/order'), [
            'as' => $this->name('tracking/order'),
            'uses' => $this->uses('trackingOrder'),
        ]);
    }

    /**Relation to controller which used by this routes */
    public function controller()
    {
        return DashboardController::class;
    }
}