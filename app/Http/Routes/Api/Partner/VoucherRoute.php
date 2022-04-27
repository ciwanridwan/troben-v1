<?php

namespace App\Http\Routes\Api\Partner;

use App\Http\Controllers\Api\Partner\VoucherController;
use Jalameta\Router\BaseRoute;

class VoucherRoute extends BaseRoute
{
    /**
     * Route path prefix.
     *
     * @var string
     */
    protected $prefix = '/voucher';

    /**
     * Registered route name.
     *
     * @var string
     */
    protected $name = 'api.partner.voucher';

    /**
     * Register routes handled by this class.
     *
     * @return void
     */
    public function register()
    {
        $this->router->get($this->prefix(''), [
            'as' => $this->name('index'),
            'uses' => $this->uses('index'),
        ]);

        $this->router->post($this->prefix(''), [
            'as' => $this->name('store'),
            'uses' => $this->uses('store'),
        ]);
    }

    /**
     * Controller used by this route.
     *
     * @return string
     */
    public function controller()
    {
        return VoucherController::class;
    }
}
