<?php

namespace App\Http\Routes\Api;

use App\Http\Controllers\Api\SlaController;
use Jalameta\Router\BaseRoute;

class SlaRoute extends BaseRoute
{
    protected $prefix = 'sla';

    protected $name = 'api.sla';

    /**
     * Register routes handled by this class.
     *
     * @return void
     */
    public function register()
    {
        $this->router->get($this->prefix('/set'), [
            'as' => $this->name,
            'uses' => $this->uses('setAlert'),
        ]);

        $this->router->get($this->prefix('/income/penalty'), [
            'as' => $this->name('income.penalty'),
            'uses' => $this->uses('incomePenalty'),
        ]);
    }

    /**
     * Controller used by this route.
     *
     * @return string
     */
    public function controller()
    {
       return SlaController::class;
    }
}
