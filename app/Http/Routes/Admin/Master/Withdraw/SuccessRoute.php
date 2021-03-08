<?php

namespace App\Http\Routes\Admin\Master\Withdraw;

use App\Http\Controllers\Admin\Master\Withdraw\SuccessController;
use Jalameta\Router\BaseRoute;

class SuccessRoute extends BaseRoute
{

    /**
     * @var string
     */
    protected $prefix = "/payment/withdraw/success";

    /**
     * @var string
     */
    protected $name = "admin.payment.withdraw.success";


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
        return SuccessController::class;
    }
}
