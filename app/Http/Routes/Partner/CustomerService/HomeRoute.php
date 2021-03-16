<?php

namespace App\Http\Routes\Partner\CustomerService;

use App\Http\Controllers\Partner\CustomerService\HomeController;
use Jalameta\Router\BaseRoute;

class HomeRoute extends BaseRoute
{
    /**
     * @var string
     */
    protected $prefix = 'home';

    /**
     * @var string
     */
    protected $name = 'partner.customer_service.home';

    /**
     * Register routes handled by this class.
     *
     * @return void
     */
    public function register()
    {
        $this->router->get($this->prefix, [
            'as' => $this->name,
            'uses' => $this->uses('index')
        ]);
    }

    /**
     * Controller used by this route.
     *
     * @return string
     */
    public function controller()
    {
        return HomeController::class;
    }
}
