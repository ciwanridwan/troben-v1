<?php

namespace App\Http\Routes\Api;

use Jalameta\Router\BaseRoute;
use App\Http\Controllers\Api\PricingController;

class PricingRoute extends BaseRoute
{
    /**
     * @var string
     */
    protected $prefix = '/pricing';

    protected $name = 'api.pricing';

    /**
     * Register routes handled by this class.
     *
     * @return void
     */
    public function register()
    {
        $this->router->get($this->prefix(), [
            'as' => $this->name,
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
        return PricingController::class;
    }
}
