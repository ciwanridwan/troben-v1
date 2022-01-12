<?php

namespace App\Http\Routes\Api\V;

use App\Http\Controllers\Api\V\SelfServiceController;
use Jalameta\Router\BaseRoute;

class SelfRoute extends BaseRoute
{
    /**
     * route prefix.
     *
     * @var string
     */
    protected $prefix = '/';

    /**
     * route name.
     *
     * @var string
     */
    protected $name = 'api.v.sf';

    /**
     * Register routes handled by this class.
     *
     * @return void
     */
    public function register()
    {
        $this->router->patch($this->prefix('package/{content}'), [
            'as' => $this->name('package.detail'),
            'uses' => $this->uses('packageDetail'),
        ]);
        $this->router->patch($this->prefix('package/{content}/update'), [
            'as' => $this->name('package.update'),
            'uses' => $this->uses('packageUpdate'),
        ]);

        $this->router->patch($this->prefix('package/{content}/cancel'), [
            'as' => $this->name('package.cancel'),
            'uses' => $this->uses('packageCancel'),
        ]);

        $this->router->patch($this->prefix('{account}/verify'), [
            'as' => $this->name('account.verify'),
            'uses' => $this->uses('accountVerify'),
        ]);

        $this->router->patch($this->prefix('delivery/{delivery_code}/destination/update'), [
            'as' => $this->name('delivery.destination.update'),
            'uses' => $this->uses('deliveryDestinationUpdate'),
        ]);

        $this->router->patch($this->prefix('delivery/{delivery_code}/package/append'), [
            'as' => $this->name('delivery.package.append'),
            'uses' => $this->uses('deliveryPackageAppend'),
        ]);
    }

    /**
     * Controller used by this route.
     *
     * @return string
     */
    public function controller(): string
    {
        return SelfServiceController::class;
    }
}
