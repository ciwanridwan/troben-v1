<?php

namespace App\Http\Routes\Api;

use App\Http\Controllers\Api\ServiceController;
use Jalameta\Router\BaseRoute;

class ServiceRoute extends BaseRoute
{
    /**
     * @var string
     */
    protected $prefix = "/service";

    /**
     * @var string
     */
    protected $name = "api.service";

    /**
     * Register routes handled by this class.
     *
     * @return void
     */
    public function register()
    {
        // GET /service => list
        $this->router->get(
            $this->prefix(),
            [
                'as' => $this->name,
                'uses' => $this->uses('list')
            ]
        );

        // PUT /service/{service_code} => update
        $this->router->put(
            $this->prefix('{code:code}'),
            [
                'as' => $this->name('update'),
                'uses' => $this->uses('update')
            ]
        );

        // POST /service => creation
        $this->router->post(
            $this->prefix(),
            [
                'as' => $this->name('create'),
                'uses' => $this->uses('creation')
            ]
        );


        // GET /service/{service_code} => show
        $this->router->get(
            $this->prefix('{code:code}'),
            [
                'as' => $this->name('show'),
                'uses' => $this->uses('show')
            ]
        );
    }

    /**
     * Controller used by this route.
     *
     * @return string
     */
    public function controller()
    {
        return ServiceController::class;
    }
}
