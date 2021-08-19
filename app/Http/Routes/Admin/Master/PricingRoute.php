<?php

namespace App\Http\Routes\Admin\Master;

use Jalameta\Router\BaseRoute;
use App\Http\Controllers\Admin\Master\PricingController;

class PricingRoute extends BaseRoute
{
    protected $name = 'admin.master.pricing.district';

    protected $prefix = '/master/pricing/district';


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
        $this->router->post($this->prefix, [
            'as' => $this->name('create'),
            'uses' => $this->uses('store'),
        ]);
        $this->router->delete($this->prefix('{hash}'), [
            'as' => $this->name('destroy'),
            'uses' => $this->uses('destroy'),
        ]);
        $this->router->patch($this->prefix('{hash}'), [
            'as' => $this->name('update'),
            'uses' => $this->uses('update'),
        ]);
        $this->router->get($this->prefix('show'), [
            'as' => $this->name('show'),
            'uses' => $this->uses('show'),
        ]);
    }

    /**
     * Controller used by this route.
     *
     * @return string
     */
    public function controller()
    {
        return PricingController::class;
    }
}
