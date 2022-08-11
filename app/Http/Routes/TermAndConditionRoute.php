<?php

namespace App\Http\Routes;

use App\Http\Controllers\Api\TermAndConditionController;
use Jalameta\Router\BaseRoute;

class TermAndConditionRoute extends BaseRoute
{
    /**
     * Route path prefix.
     *
     * @var string
     */
    protected $prefix = '/term/conditions';

    /**
     * Registered route name.
     *
     * @var string
     */

    protected $name = 'api.term.conditions';
    /**
     * Register routes handled by this class.
     *
     * @return void
     */
    public function register()
    {
        $this->router->get($this->prefix('/'), [
            'as' => $this->name('index'),
            'uses' => $this->uses('index'),
        ]);
        
        $this->router->post($this->prefix('/store'), [
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
        return TermAndConditionController::class;
    }
}