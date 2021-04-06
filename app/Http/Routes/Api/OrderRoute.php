<?php

namespace App\Http\Routes\Api;

use Jalameta\Router\BaseRoute;
use App\Http\Controllers\Api\Order\ItemController;
use App\Http\Controllers\Api\Order\OrderController;

class OrderRoute extends BaseRoute
{
    protected $prefix = 'order';

    protected $name = 'api.order';

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
        ]);

        $this->router->get($this->prefix('{package_hash}'), [
            'as' => $this->name('show'),
            'uses' => $this->uses('show'),
        ]);

        $this->router->put($this->prefix('{package_hash}'), [
            'as' => $this->name('update'),
            'uses' => $this->uses('update'),
        ]);

        $this->router->patch($this->prefix('{package_hash}/approve'), [
            'as' => $this->name('approve'),
            'uses' => $this->uses('approve'),
        ]);

        $this->router->post($this->prefix('{package_hash}/receipt'), [
            'as' => $this->name('receipt'),
            'uses' => $this->uses('receipt'),
        ]);

        $this->router->post($this->prefix('{package_hash}/item'), [
            'as' => $this->name('item.store'),
            'uses' => $this->uses('store', ItemController::class),
        ]);

        $this->router->put($this->prefix('{package_hash}/item/{item_hash}'), [
            'as' => $this->name('item.update'),
            'uses' => $this->uses('update', ItemController::class),
        ]);

        $this->router->delete($this->prefix('{package_hash}/item/{item_hash}'), [
            'as' => $this->name('item.destroy'),
            'uses' => $this->uses('destroy', ItemController::class),
        ]);
    }

    /**
     * Controller used by this route.
     *
     * @return string
     */
    public function controller()
    {
        return OrderController::class;
    }
}
