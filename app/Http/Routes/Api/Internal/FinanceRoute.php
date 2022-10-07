<?php

namespace App\Http\Routes\Api\Internal;

use App\Http\Controllers\Api\Internal\FinanceController;
use Jalameta\Router\BaseRoute;

class FinanceRoute extends BaseRoute
{
    /**
     * Route path prefix.
     *
     * @var string
     */
    protected $prefix = '/internal/finance';

    /**
     * Registered route name.
     *
     * @var string
     */
    protected $name = 'api.internal.finance';

    /**
     * Register routes handled by this class.
     *
     * @return void
     */
    public function register()
    {
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

        $this->router->get($this->prefix('/detail/{disbursment_id}'), [
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

        $this->router->get($this->prefix('/export'), [
            'as' => $this->name('export'),
            'uses' => $this->uses('export', FinanceController::class),
        ]);

        // $this->router->get($this->prefix('/detail/{id}/new'), [
        //     'as' => $this->name('new.detail'),
        //     'uses' => $this->uses('newDetail', FinanceController::class),
        // ]);
    }

    /**
     * Controller used by this route.
     *
     * @return string
     */
    public function controller()
    {
        return FinanceController::class;
    }
}
