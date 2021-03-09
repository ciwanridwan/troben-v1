<?php

namespace App\Http\Routes\Admin\Master;

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
        $this->router->get($this->prefix('/income'), [
            'as' => $this->name('income'),
            'uses' => $this->uses('index'),
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
