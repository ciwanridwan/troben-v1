<?php

namespace App\Http\Routes\Api;

use App\Http\Controllers\Api\Order\CancelController;
use Jalameta\Router\BaseRoute;
use App\Http\Controllers\Api\Order\ItemController;
use App\Http\Controllers\Api\Order\MotorBikeController;
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

        $this->router->post($this->prefix('/motorbike/store'), [
            'as' => $this->name('store-motorbike'),
            'uses' => $this->uses('store', MotorBikeController::class),
        ]);

        $this->router->post($this->prefix('/motorbike/store/item/{package_hash}'), [
            'as' => $this->name('store.motorbike.item'),
            'uses' => $this->uses('storeItem', MotorBikeController::class),
        ]);

        $this->router->get($this->prefix('find/{code_content}'), [
            'as' => $this->name('find'),
            'uses' => $this->uses('findReceipt')
        ]);

        $this->router->get($this->prefix('courier/list'), [
            'as' => $this->name('courierList'),
            'uses' => $this->uses('courierList')
        ]);

        $this->router->get($this->prefix('find'), [
            'as' => $this->name('find.public'),
            'uses' => $this->uses('findReceipt')
        ])->withoutMiddleware('api');

        $this->router->get($this->prefix('{package_hash}'), [
            'as' => $this->name('show'),
            'uses' => $this->uses('show'),
        ]);

        $this->router->put($this->prefix('{package_hash}'), [
            'as' => $this->name('update'),
            'uses' => $this->uses('update'),
        ]);

        $this->router->patch($this->prefix('{package_hash}/partner/{partner_hash}/assign'), [
            'as' => $this->name('orderAssignation'),
            'uses' => $this->uses('orderAssignation'),
        ]);

        $this->router->patch($this->prefix('{package_hash}/partner/{partner_hash}'), [
            'as' => $this->name('fusion'),
            'uses' => $this->uses('fusion'),
        ]);

        $this->router->patch($this->prefix('{package_hash}/approve'), [
            'as' => $this->name('approve'),
            'uses' => $this->uses('approve'),
        ]);

        $this->router->patch($this->prefix('{package_hash}/cancel'), [
            'as' => $this->name('cancel'),
            'uses' => $this->uses('cancel', CancelController::class),
        ]);

        $this->router->post($this->prefix('{package_hash}/cancel'), [
            'as' => $this->name('cancelOrder'),
            'uses' => $this->uses('cancelOrder', CancelController::class),
        ]);

        $this->router->patch($this->prefix('{package_hash}/cancel/method'), [
            'as' => $this->name('cancel.method'),
            'uses' => $this->uses('method', CancelController::class),
        ]);

        $this->router->patch($this->prefix('{package_hash}/cancelBefore'), [
            'as' => $this->name('cancelBefore'),
            'uses' => $this->uses('cancelBefore', CancelController::class),
        ]);

        $this->router->get($this->prefix('{package_hash}/check-cancel-payment'), [
            'as' => $this->name('checkCancelPayment'),
            'uses' => $this->uses('checkCancelPayment', CancelController::class)
        ]);

        $this->router->post($this->prefix('{package_hash}/receipt'), [
            'as' => $this->name('receipt'),
            'uses' => $this->uses('receipt'),
        ]);

        $this->router->post($this->prefix('{package_hash}/item'), [
            'as' => $this->name('item.store'),
            'uses' => $this->uses('store', ItemController::class),
        ]);

        $this->router->post($this->prefix('{package_hash}/item-motorbike'), [
            'as' => $this->name('item.store-motorbike'),
            'uses' => $this->uses('storeItem', MotorBikeController::class),
        ]);

        $this->router->put($this->prefix('{package_hash}/item'), [
            'as' => $this->name('item.update'),
            'uses' => $this->uses('update', ItemController::class),
        ]);

        $this->router->delete($this->prefix('{package_hash}/item/{item_hash}'), [
            'as' => $this->name('item.destroy'),
            'uses' => $this->uses('destroy', ItemController::class),
        ]);

        $this->router->get($this->prefix('ship/schedule'), [
            'as' => $this->name('shipSchedule'),
            'uses' => $this->uses('shipSchedule'),
        ]);

        $this->router->get($this->prefix('personal/data'), [
            'as' => $this->name('usePersonalData'),
            'uses' => $this->uses('usePersonalData'),
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
