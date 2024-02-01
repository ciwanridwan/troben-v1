<?php

namespace App\Http\Routes\Api\Internal;

use App\Http\Controllers\Api\Internal\FinanceController;
use Jalameta\Router\BaseRoute;
use App\Http\Controllers\Api\Partner\Owner\WithdrawalController;

class WithdrawalRoute extends BaseRoute
{
    /**
     * Route path prefix.
     *
     * @var string
     */
    protected $prefix = '/internal/finance/withdrawal';

    /**
     * Registered route name.
     *
     * @var string
     */
    protected $name = 'api.internal.finance.withdrawal';

    /**
     * Register routes handled by this class.
     *
     * @return void
     */
    public function register()
    {
        $this->router->get($this->prefix('/list'), [//
            'as' => $this->name('list'),
            'uses' => $this->uses('list', FinanceController::class),
        ]);

        $this->router->get($this->prefix('/list_paginate'), [
            'as' => $this->name('listPaginate'),
            'uses' => $this->uses('listPaginate', FinanceController::class),
        ]);

        $this->router->get($this->prefix('/count/amount'), [//
            'as' => $this->name('countAmountDisbursment'),
            'uses' => $this->uses('countAmountDisbursment', FinanceController::class),
        ]);

        $this->router->get($this->prefix('/count'), [//
            'as' => $this->name('countDisbursment'),
            'uses' => $this->uses('countDisbursment', FinanceController::class),
        ]);

        $this->router->get($this->prefix('/list/partners'), [//
            'as' => $this->name('listPartners'),
            'uses' => $this->uses('listPartners', FinanceController::class),
        ]);

        $this->router->get($this->prefix('/detail/{id}/ajax'), [//
            'as' => $this->name('detailAjax'),
            'uses' => $this->uses('detail', FinanceController::class),
        ]);

        $this->router->get($this->prefix('/detail/{id}/find/receipt'), [//
            'as' => $this->name('findByReceipt'),
            'uses' => $this->uses('findByReceipt', FinanceController::class),
        ]);

        // report
        $this->router->get($this->prefix('/report'), [
            'as' => $this->name('report'),
            'uses' => $this->uses('reportReceipt', FinanceController::class),
        ]);

        $this->router->get($this->prefix('/export'), [
            'as' => $this->name('export'),
            'uses' => $this->uses('export', FinanceController::class)
        ]);
        // report end

        // action
        $this->router->post($this->prefix('/detail/{id}/approve'), [//
            'as' => $this->name('approve'),
            'uses' => $this->uses('approve', FinanceController::class),
        ]);

        $this->router->post($this->prefix('/attachment_transfer/{id}'), [//
            'as' => $this->name('attachmentTransfer'),
            'uses' => $this->uses('attachmentTransfer', WithdrawalController::class)
        ]);

        $this->router->get($this->prefix('/components'), [//
            'as' => $this->name('components'),
            'uses' => $this->uses('incomeComponents', FinanceController::class)
        ]);
        // action end
    }
}
