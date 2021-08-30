<?php

namespace App\Http\Routes\Api;

use App\Http\Controllers\Api\PromoController;
use Jalameta\Router\BaseRoute;

class PromoRoute extends BaseRoute
{
    /**
     * Route path prefix.
     *
     * @var string
     */
    protected $prefix = '/promo';

    /**
     * Registered route name.
     *
     * @var string
     */
    protected $name = 'api.promo';

    /**
     * Register routes handled by this class.
     *
     * @return void
     */
    public function register()
    {
        $this->router->get($this->prefix(), [
            'as' => $this->name(),
            'uses' => $this->uses('index'),
        ])->withoutMiddleware('api');

        $this->router->post($this->prefix(), [
            'as' => $this->name('store'),
            'uses' => $this->uses('store'),
        ])->withoutMiddleware('api');
    }

    /**
     * Controller used by this route.
     *
     * @return string
     */
    public function controller()
    {
        return PromoController::class;
    }
}
