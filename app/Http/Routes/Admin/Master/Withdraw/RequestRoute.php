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

        $this->router->get($this->prefix('detail'), [
            'as' => $this->name('detail'),
            'uses' => $this->uses('detail'),
        ]);

        $this->router->get($this->prefix('/list'), [
            'as' => $this->name('list'),
            'uses' => $this->uses('list', FinanceController::class),
        ]);

        $this->router->get($this->prefix('/count/amount'), [
            'as' => $this->name('countAmountDisbursment'),
            'uses' => $this->uses('countAmountDisbursment', FinanceController::class),
        ]);

        $this->router->get($this->prefix('/count'), [
            'as' => $this->name('countDisbursment'),
            'uses' => $this->uses('countDisbursment', FinanceController::class),
        ]);

        $this->router->get($this->prefix('/find/partner'), [
            'as' => $this->name('findByPartner'),
            'uses' => $this->uses('findByPartner', FinanceController::class),
        ]);

        $this->router->get($this->prefix('/find/status'), [
            'as' => $this->name('findByStatus'),
            'uses' => $this->uses('findByStatus', FinanceController::class),
        ]);

        $this->router->get($this->prefix('/find/date'), [
            'as' => $this->name('findByDate'),
            'uses' => $this->uses('findByDate', FinanceController::class),
        ]);

        $this->router->get($this->prefix('/detail/{id}'), [
            'as' => $this->name('detail'),
            'uses' => $this->uses('detail', FinanceController::class),
        ]);

        $this->router->get($this->prefix('/detail/{id}/ajax'), [
            'as' => $this->name('detailAjax'),
            'uses' => $this->uses('detail', FinanceController::class),
        ]);
        
        $this->router->post($this->prefix('/detail/{id}/approve'), [
            'as' => $this->name('approve'),
            'uses' => $this->uses('approve', FinanceController::class),
        ]);

        $this->router->get($this->prefix('/detail/{id}/find/receipt'), [
            'as' => $this->name('findByReceipt'),
            'uses' => $this->uses('findByReceipt', FinanceController::class),
        ]);

        $this->router->get($this->prefix('/list/partners'), [
            'as' => $this->name('listPartners'),
            'uses' => $this->uses('listPartners', FinanceController::class),
        ]);

        $this->router->get($this->prefix('/report'), [
            'as' => $this->name('report'),
            'uses' => $this->uses('reportReceipt', FinanceController::class),
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
