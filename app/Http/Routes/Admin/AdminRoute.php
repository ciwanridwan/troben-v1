<?php

namespace App\Http\Routes\Admin;

use App\Http\Controllers\Admin\MessageController;
use App\Http\Controllers\Partner\NotificationController;
use Jalameta\Router\BaseRoute;

class AdminRoute extends BaseRoute
{
    protected $name = 'admin';

    /**
     * Register routes handled by this class.
     *
     * @return void
     */
    public function register()
    {
        $this->router->get($this->prefix('notification'), [
            'as' => $this->name('notification'),
            'uses' => $this->uses('index', NotificationController::class),
        ]);

        $this->router->patch($this->prefix('notification/{notification_id}'), [
            'as' => $this->name('notification.read'),
            'uses' => $this->uses('read', NotificationController::class),
        ]);

        $this->router->get($this->prefix('message'), [
            'as' => $this->name('message'),
            'uses' => $this->uses('index', MessageController::class),
        ]);
    }
}
