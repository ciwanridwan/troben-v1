<?php

namespace App\Http\Routes\Partner;

use App\Http\Controllers\Partner\NotificationController;
use Jalameta\Router\BaseRoute;

class PartnerRoute extends BaseRoute
{
    protected $name = 'partner';

    /**
     * Register routes handled by this class.
     *
     * @return void
     */
    public function register()
    {
        $this->router->get($this->prefix('notification'),[
            'as' => $this->name('notification'),
            'uses' => $this->uses('index', NotificationController::class),
        ]);

        $this->router->patch($this->prefix('notification/{notification_id}'),[
            'as' => $this->name('notification.read'),
            'uses' => $this->uses('read', NotificationController::class),
        ]);
    }
}
