<?php

namespace App\Http\Routes;

use App\Models\Packages\Item;
use Jalameta\Router\BaseRoute;
use App\Models\Packages\Package;
use App\Models\Deliveries\Delivery;
use App\Models\Partners\Pivot\UserablePivot;
use App\Models\Partners\Transporter;

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
        $this->router->bind('delivery_hash', fn ($hash) => Delivery::byHashOrFail($hash));
        $this->router->bind('transporter_hash', fn ($hash) => Transporter::byHashOrFail($hash));
        $this->router->bind('userable_hash', fn ($hash) => UserablePivot::byHashOrFail($hash));
        $this->router->bind('item_hash', fn ($hash) => Item::byHashOrFail($hash));
    }
}
