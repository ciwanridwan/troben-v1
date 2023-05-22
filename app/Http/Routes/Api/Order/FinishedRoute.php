<?php

namespace App\Http\Routes\Api\Order;

use App\Http\Controllers\Api\Order\ComplaintController;
use App\Http\Controllers\Api\Order\RatingAndReviewController;
use Jalameta\Router\BaseRoute;

class FinishedRoute extends BaseRoute
{
    /**
     * Define prefix
     */
    protected $prefix = 'order/';

    /**
     * Define name of routes
     */
    protected $name = 'order';
    /**
     * Register routes handled by this class.
     *
     * @return void
     */
    public function register()
    {
        $this->router->post($this->prefix('/finish'), [
            'as' => $this->name('finish'),
            'uses' => $this->uses('store', RatingAndReviewController::class),
        ]);

        $this->router->post($this->prefix('/complaint'), [
            'as' => $this->name('complaint'),
            'uses' => $this->uses('store', ComplaintController::class),
        ]);
    }
}
