<?php

namespace App\Http\Routes\Admin\Master\Withdraw;

use App\Http\Controllers\Admin\Master\Withdraw\RequestController;
use Jalameta\Router\BaseRoute;

class RequestRoute extends BaseRoute
{
    protected $prefix = "/payment/withdraw/request";

    protected $name = "admin.payment.withdraw.request";

    /**
     * Register routes handled by this class.
     *
     * @return void
     */
    public function register()
    {
        $this->router->get($this->prefix, [
            'as' => $this->name,
            'uses' => $this->uses('index')
        ]);
    }

    /**
     * Controller used by this route.
     *
     * @return string
     */
    public function controller()
    {
        return RequestController::class;
    }
}
