<?php

namespace App\Http\Routes\Api;

use App\Http\Controllers\Api\ProductController;
use Jalameta\Router\BaseRoute;

class ProductRoute extends BaseRoute
{
    /**
     * @var string
     */
    protected $prefix = '/product';

    /**
     * @var string
     */
    protected $name = 'api.product';

    /**
     * Register routes handled by this class.
     *
     * @return void
     */
    public function register()
    {
        // GET /product => list
        $this->router->get(
            $this->prefix(),
            [
                'as' => $this->name,
                'uses' => $this->uses('list'),
            ]
        );
    }

    /**
     * Controller used by this route.
     *
     * @return string
     */
    public function controller()
    {
        return ProductController::class;
    }
}
