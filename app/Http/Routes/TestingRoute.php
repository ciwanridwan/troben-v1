<?php

namespace App\Http\Routes;

use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Partner\Cashier\HomeController as CashierHomeController;
use Jalameta\Router\BaseRoute;

class TestingRoute extends BaseRoute
{
    /**
     * Route path prefix.
     *
     * @var string
     */
    protected $prefix = '/test';

    /**
     * Registered route name.
     *
     * @var string
     */
    protected $name = 'api.test';

    /**
     * Register routes handled by this class.
     *
     * @return void
     */
    public function register()
    {
        $this->router->get($this->prefix('/admin/home'), [
            'as' => $this->name('admin.home'),
            'uses' => $this->uses('index', HomeController::class),
        ]);

        $this->router->patch($this->prefix('/cashier/discount/{package_hash}'), [
            'as' => $this->name('cashier.discount'),
            'uses' => $this->uses('packageChecked', CashierHomeController::class),
        ]);

        // $this->router->patch($this->prefix('/cashier/package/checked/{package_hash}'), [
        //     'as' => $this->name('cashier.discount'),
        //     'uses' => $this->uses('packageChecked', CashierHomeController::class),
        // ]);
    }
}
