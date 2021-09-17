<?php

namespace App\Http\Routes\Api\WMS\Warehouse\Manifest;

use App\Http\Controllers\Api\WMS\Warehouse\Manifest\TransitController;
use Jalameta\Router\BaseRoute;

class TransitRoute extends BaseRoute
{
    protected $prefix = 'wms/warehouse/manifest/transit';

    protected $name = 'api.wms.warehouse.manifest.transit';

    /**
     * Register routes handled by this class.
     *
     * @return void
     */
    public function register()
    {
        $this->router->patch($this->prefix('unload/{delivery_hash}/package'), [
            'as' => $this->name('unload'),
            'uses' => $this->uses('unload'),
        ]);
    }

    /**
     * Controller used by this route.
     *
     * @return string
     */
    public function controller()
    {
        return TransitController::class;
    }
}
