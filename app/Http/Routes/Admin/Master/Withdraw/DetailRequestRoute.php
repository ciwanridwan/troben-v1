<?php

namespace App\Http\Routes\Admin\Master\Withdraw;

use Jalameta\Router\BaseRoute;
use App\Http\Controllers\Admin\Master\Withdraw\DetailRequestController;

class DetailRequestRoute extends BaseRoute
{
    /**Add new route for detail request */
    protected $prefix = '/payment/withdraw/request/detail/{withdrawl_hash}';

    protected $name = 'admin.payment.withdraw.request.detail';
    /**
     * Register routes handled by this class.
     *
     * @return void
     */
    public function register()
    {
        // make an awesome route for detail request
        $this->router->get($this->prefix, [
            'as' => $this->name,
            'uses' => $this->uses('index'),
        ]);
    }


    /**Relation to controller */
    public function controller()
    {
        return DetailRequestController::class;
    }
}
