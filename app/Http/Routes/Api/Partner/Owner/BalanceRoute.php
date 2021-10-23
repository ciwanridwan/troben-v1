<?php

namespace App\Http\Routes\Api\Partner\Owner;

use App\Http\Controllers\Api\Partner\Owner\BalanceController;
use Jalameta\Router\BaseRoute;

class BalanceRoute extends BaseRoute
{
    protected $name = 'api.partner.owner.balance';

    protected $prefix = 'partner/owner/balance';

    protected $middleware = [
        'partner.role:owner',
        'partner.scope.role:owner',
    ];

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

        $this->router->get($this->prefix('summary'), [
            'as' => $this->name('summary'),
            'uses' => $this->uses('summary'),
        ]);

        $this->router->get($this->prefix('detail'), [
            'as' => $this->name('detail'),
            'uses' => $this->uses('detail'),
        ]);
    }

    /**
     * Controller used by this route.
     *
     * @return string
     */
    public function controller()
    {
        return BalanceController::class;
    }
}
