<?php

namespace App\Http\Routes\Admin\Master\Withdraw;

use Jalameta\Router\BaseRoute;
use App\Http\Controllers\Admin\Master\Withdraw\PendingController;

class PendingRoute extends BaseRoute
{
    /**
     * @var string
     */
    protected $prefix = '/payment/withdraw/pending';

    /**
     * @var string
     */
    protected $name = 'admin.payment.withdraw.pending';

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

        $this->router->patch($this->prefix('success/{withdrawal_hash}'), [
            'as' => $this->name('success'),
            'uses' => $this->uses('success'),
        ]);

        $this->router->patch($this->prefix('cancelled/{withdrawal_hash}'), [
            'as' => $this->name('rejection'),
            'uses' => $this->uses('rejection'),
        ]);
    }

    /**
     * Controller used by this route.
     *
     * @return string
     */
    public function controller()
    {
        return PendingController::class;
    }
}
