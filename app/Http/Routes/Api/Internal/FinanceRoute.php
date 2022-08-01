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
            'uses' => $this->uses('list'),
        ]);

        $this->router->get($this->prefix('/list/partners'), [
            'as' => $this->name('listPartners'),
            'uses' => $this->uses('listPartners'),
        ]);

        $this->router->get($this->prefix('/detail/{id}'), [
            'as' => $this->name('detail'),
            'uses' => $this->uses('detail'),
        ]);

        /** Route fixed can error 419 */
        // $this->router->post($this->prefix('/detail/{withdrawal_hash}/approve'), [
        //     'as' => $this->name('approve'),
        //     'uses' => $this->uses('approve'),
        // ]);

        /** Route temporary*/
        $this->router->post($this->prefix('/detail/{id}/approve'), [
            'as' => $this->name('approve'),
            'uses' => $this->uses('approve'),
        ])->withoutMiddleware('api');

        $this->router->get($this->prefix('/find/partner'), [
            'as' => $this->name('findByPartner'),
            'uses' => $this->uses('findByPartner'),
        ]);

        $this->router->get($this->prefix('/find/status'), [
            'as' => $this->name('findByStatus'),
            'uses' => $this->uses('findByStatus'),
        ]);

        $this->router->get($this->prefix('/find/date'), [
            'as' => $this->name('findByDate'),
            'uses' => $this->uses('findByDate'),
        ]);

        $this->router->get($this->prefix('/detail/{id}/find/receipt'), [
            'as' => $this->name('findByReceipt'),
            'uses' => $this->uses('findByReceipt'),
        ]);

        $this->router->get($this->prefix('/count'), [
            'as' => $this->name('countDisbursment'),
            'uses' => $this->uses('countDisbursment'),
        ]);

        $this->router->get($this->prefix('/count/amount'), [
            'as' => $this->name('countAmountDisbursment'),
            'uses' => $this->uses('countAmountDisbursment'),
        ]);

        $this->router->get($this->prefix('/report'), [
            'as' => $this->name('report'),
            'uses' => $this->uses('reportReceipt'),
        ]);
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
