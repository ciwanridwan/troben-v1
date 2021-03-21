<?php

namespace App\Http\Routes\Api\Order;

use App\Models\Packages\Item;
use Jalameta\Router\BaseRoute;
use App\Http\Controllers\Api\Order\ItemController;

class ItemRoute extends BaseRoute
{
    protected $prefix = 'order/{package_hash}/item';

    protected $name = 'api.order.item';

    /**
     * Register routes handled by this class.
     *
     * @return void
     */
    public function register()
    {
        $this->router->bind('item_hash', fn ($hash) => Item::byHashOrFail($hash));

        $this->router->post($this->prefix(), [
            'as' => $this->name('store'),
            'uses' => $this->uses('store'),
        ]);

        $this->router->put($this->prefix('{item_hash}'), [
            'as' => $this->name('update'),
            'uses' => $this->uses('update'),
        ]);

        $this->router->delete($this->prefix('{item_hash}'), [
            'as' => $this->name('destroy'),
            'uses' => $this->uses('destroy'),
        ]);
    }

    /**
     * Controller used by this route.
     *
     * @return string
     */
    public function controller()
    {
        return ItemController::class;
    }
}
