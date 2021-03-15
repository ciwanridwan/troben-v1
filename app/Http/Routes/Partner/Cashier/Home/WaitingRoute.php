<?php

namespace App\Http\Routes\Partner\Cashier\Home;

use Jalameta\Router\BaseRoute;
use App\Http\Controllers\Partner\Cashier\Home\WaitingController;

class WaitingRoute extends BaseRoute
{
    /**
     * @var string
     */
    protected $prefix = 'home/waiting';

    /**
     * @var string
     */
    protected $name = 'partner.cashier.home.waiting.confirmation';

    /**
     * Register routes handled by this class.
     *
     * @return void
     */
    public function register()
    {
        $this->router->get($this->prefix('customer'), [
            'as' => $this->name('customer'),
            'uses' => $this->uses('customer_view'),
        ]);

        $this->router->get($this->prefix('payment'), [
            'as' => $this->name('payment'),
            'uses' => $this->uses('payment_view'),
        ]);

        $this->router->get($this->prefix('revision'), [
            'as' => $this->name('revision'),
            'uses' => $this->uses('revision_view'),
        ]);
    }

    /**
     * Controller used by this route.
     *
     * @return string
     */
    public function controller()
    {
        return WaitingController::class;
    }
}
