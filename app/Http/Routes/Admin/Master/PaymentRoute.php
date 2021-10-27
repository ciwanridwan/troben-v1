<?php

namespace App\Http\Routes\Admin\Master;

use App\Http\Controllers\Admin\Master\Payment\ReportController;
use App\Http\Controllers\Api\GeoController;
use Jalameta\Router\BaseRoute;
use App\Http\Controllers\Admin\Master\PaymentController;

class PaymentRoute extends BaseRoute
{
    /**
     * @var string
     */
    protected $name = 'admin.payment';

    /**
     * @var string
     */
    protected $prefix = '/payment';

    /**
     * Register routes handled by this class.
     *
     * @return void
     */
    public function register()
    {
        $this->router->get($this->prefix('/home'), [
            'as' => $this->name('home'),
            'uses' => $this->uses('home'),
        ]);
        $this->router->get($this->prefix('/income'), [
            'as' => $this->name('income'),
            'uses' => $this->uses('index'),
        ]);

        $this->router->get($this->prefix('/mtak'), [
            'as' => $this->name('mtak'),
            'uses' => $this->uses('getIncomeMTAK'),
        ]);

        $this->router->get($this->prefix('/mtakab'), [
            'as' => $this->name('mtakab'),
            'uses' => $this->uses('getIncomeMTAKab'),
        ]);

        $this->router->get($this->prefix('/mpw'), [
            'as' => $this->name('mpw'),
            'uses' => $this->uses('getIncomeMPW'),
        ]);

        $this->router->get($this->prefix('/ms'), [
            'as' => $this->name('ms'),
            'uses' => $this->uses('getIncomeSpace'),
        ]);

        $this->router->get($this->prefix('data'), [
            'as' => $this->name('data'),
            'uses' => $this->uses('data', ReportController::class),
        ]);
        $this->router->get($this->prefix('geo'), [
            'as' => $this->name('geo'),
            'uses' => $this->uses('index', GeoController::class)
        ]);

        $this->router->get($this->prefix('/partner'), [
            'as' => $this->name('partner'),
            'uses' => $this->uses('getPartnerList'),
        ]);
    }

    /**
     * Controller used by this route.
     *
     * @return string
     */
    public function controller()
    {
        return PaymentController::class;
    }
}
