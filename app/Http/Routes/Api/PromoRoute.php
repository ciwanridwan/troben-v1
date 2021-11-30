<?php

namespace App\Http\Routes\Api;

use App\Http\Controllers\Api\Promote\PromoController;
use App\Http\Controllers\Api\Promote\PromotionController;
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
            'as' => $this->name,
            'uses' => $this->uses('index'),
        ]);

        $this->router->post($this->prefix(), [
            'as' => $this->name('store'),
            'uses' => $this->uses('store'),
        ])->withoutMiddleware('api');

        $this->router->get($this->prefix('promotion/list'), [
            'as' => $this->name('promotion.list'),
            'uses' => $this->uses('index', PromotionController::class),
        ]);

        $this->router->get($this->prefix('promotion/show/{promotion_hash}/{package_hash}'), [
            'as' => $this->name('promotion.show'),
            'uses' => $this->uses('show', PromotionController::class),
        ]);

        $this->router->post($this->prefix('promotion/calculate/{package_hash}'), [
            'as' => $this->name('promotion.calculate'),
            'uses' => $this->uses('calculate', PromotionController::class),
        ]);

        $this->router->post($this->prefix('promotion/store'), [
            'as' => $this->name('promotion.store'),
            'uses' => $this->uses('store',PromotionController::class),
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
