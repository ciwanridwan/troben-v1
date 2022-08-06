<?php

namespace App\Http\Routes\Admin\Master\Withdraw;

use Jalameta\Router\BaseRoute;
use App\Http\Controllers\Admin\Master\Withdraw\RequestController;
use App\Http\Controllers\Api\Internal\FinanceController;

class RequestRoute extends BaseRoute
{
    protected $prefix = '/payment/withdraw/request';

    protected $name = 'admin.payment.withdraw.request';

    /**
     * Register routes handled by this class.
     *
     * @return void
     */
    public function register()
    {
        $this->router->get($this->prefix, [
            'as' => $this->name,
            'uses' => $this->uses('index'),
        ]);
        $this->router->patch($this->prefix('confirmation/{withdrawal_hash}'), [
            'as' => $this->name('confirmation'),
            'uses' => $this->uses('confirmation'),
        ]);
        $this->router->patch($this->prefix('rejection/{withdrawal_hash}'), [
            'as' => $this->name('rejection'),
            'uses' => $this->uses('rejection'),
        ]);

        // ajax
        $this->router->get($this->prefix('/find/partner'), [
            'as' => $this->name('findByPartner'),
            'uses' => $this->uses('findByPartner', FinanceController::class),
        ]);
        $this->router->get($this->prefix('/count/amount'), [
            'as' => $this->name('countAmountDisbursment'),
            'uses' => $this->uses('countAmountDisbursment', FinanceController::class),
        ]);
        $this->router->get($this->prefix('/count'), [
            'as' => $this->name('countDisbursment'),
            'uses' => $this->uses('countDisbursment', FinanceController::class),
        ]);
        $this->router->get($this->prefix('/list'), [
            'as' => $this->name('list'),
            'uses' => $this->uses('list', FinanceController::class),
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
