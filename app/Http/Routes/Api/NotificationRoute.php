<?php

namespace App\Http\Routes\Api;

use App\Http\Controllers\Partner\NotificationController;
use Jalameta\Router\BaseRoute;

class NotificationRoute extends BaseRoute
{
    protected $name = 'api.notification';

    protected $prefix = 'notification';

    /**
     * Register routes handled by this class.
     *
     * @return void
     */
    public function register()
    {
        $this->router->get($this->prefix, [
            'as' => $this->name,
            'uses' => $this->uses('index'),
        ]);

        $this->router->patch($this->prefix('{notification_id}'), [
            'as' => $this->name('read'),
            'uses' => $this->uses('read'),
        ]);

        $this->router->get($this->prefix('counter'),[
            'as' => $this->name('counter'),
            'uses' => $this->uses('counter')
        ]);
    }

    /**
     * Controller used by this route.
     *
     * @return string
     */
    public function controller()
    {
        return NotificationController::class;
    }
}
