<?php

namespace App\Http\Routes\Api;

use Jalameta\Router\BaseRoute;
use App\Models\Packages\Package;
use App\Http\Controllers\Api\OrderController;

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
        $this->router->bind('package_hash', fn ($hash) => Package::byHashOrFail($hash));

        $this->router->get($this->prefix(), [
            'as' => $this->name,
            'uses' => $this->uses('index'),
        ]);

        $this->router->get($this->prefix('{package_hash}'), [
            'as' => $this->name('show'),
            'uses' => $this->uses('show'),
        ]);

        $this->router->put($this->prefix('{package_hash}'), [
            'as' => $this->name('update'),
            'uses' => $this->uses('update'),
        ]);

        $this->router->post($this->prefix(), [
            'as' => $this->name('store'),
            'uses' => $this->uses('store'),
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
