<?php

namespace App\Http\Routes\Partner\CustomerService;

use App\Http\Controllers\Partner\CustomerService\MessageController;
use Jalameta\Router\BaseRoute;

class CustomerServiceRoute extends BaseRoute
{
    protected $name = 'partner.customer_service';

    /**
     * Register routes handled by this class.
     *
     * @return void
     */
    public function register()
    {
        $this->router->get($this->prefix('message'), [
            'as' => $this->name('message'),
            'uses' => $this->uses('index', MessageController::class),
        ]);
    }
}
