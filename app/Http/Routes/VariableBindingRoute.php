<?php

namespace App\Http\Routes;

use App\Models\Code;
use App\Models\Customers\Customer;
use App\Models\Packages\Item;
use App\Models\Payments\Gateway;
use Jalameta\Router\BaseRoute;
use App\Models\Packages\Package;
use App\Models\Partners\Partner;
use App\Models\Deliveries\Delivery;
use App\Models\Partners\Transporter;
use App\Models\Partners\Pivot\UserablePivot;

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
        $this->router->bind('partner_hash', fn ($hash) => Partner::byHashOrFail($hash));
        $this->router->bind('code_content', fn ($code) => Code::where('content', $code)->firstOrFail());
        $this->router->bind('gateway_hash', fn ($hash) => Gateway::byHashOrFail($hash));
        $this->router->bind('customer_hash', fn ($hash) => Customer::byHashOrFail($hash));
    }
}
