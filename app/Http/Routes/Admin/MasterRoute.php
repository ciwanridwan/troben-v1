<?php

namespace App\Http\Routes\Admin;

use Jalameta\Router\BaseRoute;
use App\Http\Controllers\Admin\MasterController;

class MasterRoute extends BaseRoute
{
    /**
     * @var string
     */
    protected $prefix = '/master';

    /**
     * @var string
     */
    protected $name = 'admin.master';

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
        $this->router->get($this->prefix('/ongkir-kecamatan'), [
            'as' => $this->name('charge.district'),
            'uses' => $this->uses('charge_district'),
        ]);
        $this->router->get($this->prefix('/customer'), [
            'as' => $this->name('customer'),
            'uses' => $this->uses('customer'),
        ]);
    }

    /**
     * Controller used by this route.
     *
     * @return string
     */
    public function controller()
    {
        return MasterController::class;
    }
}
