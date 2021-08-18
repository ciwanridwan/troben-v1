<?php

namespace App\Http\Routes\Partner\Cashier;

use App\Http\Controllers\Admin\Master\PricingController;
use Jalameta\Router\BaseRoute;
use App\Http\Controllers\Partner\Cashier\HomeController;

class HomeRoute extends BaseRoute
{
    protected $prefix = 'home';

    protected $name = 'partner.cashier.home';

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
        $this->router->patch($this->prefix('{package_hash}/{item_hash}'), [
            'as' => $this->name('updatePackageItem'),
            'uses' => $this->uses('updatePackageItem'),
        ]);
        $this->router->patch($this->prefix('{package_hash}'), [
            'as' => $this->name('packageChecked'),
            'uses' => $this->uses('packageChecked'),
        ]);
        $this->router->delete($this->prefix('{package_hash}/{item_hash}'), [
            'as' => $this->name('deletePackageItem'),
            'uses' => $this->uses('deletePackageItem'),
        ]);
        $this->router->get($this->prefix('price'), [
            'as' => $this->name('price'),
            'uses' => $this->uses('show', PricingController::class)
        ]);
    }

    /**
     * Controller used by this route.
     *
     * @return string
     */
    public function controller()
    {
        return HomeController::class;
    }
}
