<?php

namespace App\Http\Routes\Api;

use Jalameta\Router\BaseRoute;
use App\Http\Controllers\Api\ProductController;

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

        // public route
        $this->router->get('public/'.$this->prefix(), [
            'as' => 'promot.'.$this->name,
            'uses' => $this->uses('list'),
        ])->withoutMiddleware('api');
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
