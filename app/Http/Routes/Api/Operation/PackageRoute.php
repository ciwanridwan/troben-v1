<?php

namespace App\Http\Routes\Api\Operation;

use Jalameta\Router\BaseRoute;
use App\Http\Controllers\Api\Operation\PackageController;

class PackageRoute extends BaseRoute
{
    /**
     * route prefix.
     *
     * @var string
     */
    protected $prefix = '/package';

    /**
     * route name.
     *
     * @var string
     */
    protected $name = 'api.operation.package';

    /**
     * Register routes handled by this class.
     *
     * @return void
     */
    public function register()
    {
        $this->router->patch($this->prefix('update/{content}/payment/status'), [
            'as' => $this->name('update.payment.status'),
            'uses' => $this->uses('updatePaymentStatus'),
        ]);

        $this->router->patch($this->prefix('update/{content}/status'), [
            'as' => $this->name('update.status'),
            'uses' => $this->uses('updateStatus'),
        ]);
    }

    /**
     * Controller used by this route.
     *
     * @return string
     */
    public function controller(): string
    {
        return PackageController::class;
    }
}
