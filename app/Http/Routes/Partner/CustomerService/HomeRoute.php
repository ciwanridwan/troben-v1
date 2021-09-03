<?php

namespace App\Http\Routes\Partner\CustomerService;

use Jalameta\Router\BaseRoute;
use App\Http\Controllers\Partner\CustomerService\HomeController;

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
            'uses' => fn () => redirect(route('partner.customer_service.home.order.pickup'))
        ]);

        $this->router->get($this->prefix('done'), [
            'as' => $this->name('done'),
            'uses' => $this->uses('done'),
        ]);

        $this->router->get($this->prefix('processed'), [
            'as' => $this->name('processed'),
            'uses' => $this->uses('processed'),
        ]);

        $this->router->get($this->prefix('cancel'), [
            'as' => $this->name('cancel'),
            'uses' => $this->uses('cancel'),
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
