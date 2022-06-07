<?php

namespace App\Http\Routes\Api\Partner\Owner;

use App\Http\Controllers\Api\Partner\Owner\ScheduleTransportationController;
use Jalameta\Router\BaseRoute;

class ScheduleTransportationRoute extends BaseRoute
{
    protected $prefix = 'partner/owner/schedule';

    protected $name = 'api.partner.owner.schedule';

    protected $middleware = [
        'partner.role:owner',
        'partner.scope.role:owner',
    ];
    /**
     * Register routes handled by this class.
     *
     * @return void
     */
    public function register()
    {
        $this->router->get($this->prefix(), [
            'as' => $this->name('index'),
            'uses' => $this->uses('index'),
        ]);

        $this->router->post($this->prefix(), [
            'as' => $this->name('store'),
            'uses' => $this->uses('store'),
        ]);

        $this->router->delete($this->prefix(), [
            'as' => $this->name('destroy'),
            'uses' => $this->uses('destroy'),
        ]);

        $this->router->put($this->prefix(), [
            'as' => $this->name('update'),
            'uses' => $this->uses('update'),
        ]);

        // Add new route for view in landing page
        $this->router->get($this->prefix('/show'), [
            'as' => $this->name('showInFront'),
            'uses' => $this->uses('showInFront'),
        ]);

        // Adding route for show with harbor
        $this->router->get($this->prefix('/harbor/show'), [
            'as' => $this->name('showWithHarbor'),
            'uses' => $this->uses('showWithHarbor'),
        ])->withoutMiddleware('api');
    }

    /**
     * Controller used by this route.
     *
     * @return string
     */
    public function controller()
    {
        return ScheduleTransportationController::class;
    }
}
