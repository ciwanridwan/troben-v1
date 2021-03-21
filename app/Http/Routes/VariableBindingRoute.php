<?php

namespace App\Http\Routes;

use App\Models\Packages\Item;
use Jalameta\Router\BaseRoute;
use App\Models\Packages\Package;

class VariableBindingRoute extends BaseRoute
{
    /**
     * Register routes handled by this class.
     *
     * @return void
     */
    public function register()
    {
        $this->router->bind('package_hash', fn ($hash) => Package::byHashOrFail($hash));
        $this->router->bind('item_hash', fn ($hash) => Item::byHashOrFail($hash));
    }
}
