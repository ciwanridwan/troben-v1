<?php

namespace App\Http\Routes\Admin\Master\AccountExecutive;

use App\Http\Controllers\AccountExecutive\AgentController;
use Jalameta\Router\BaseRoute;

class AgentRoute extends BaseRoute
{
    /**
     * Register routes handled by this class.
     *
     * @return void
     */
    protected $prefix = '/account/executive/agent/';

    protected $name = 'admin.master.account.executive.agent';

    public function register()
    {
        $this->router->get($this->prefix('index'), [
            'as' => $this->name('index'),
            'uses' => $this->uses('index'),
        ]);

        $this->router->get($this->prefix('detail/{user_id}/{period}'), [
            'as' => $this->name('detail'),
            'uses' => $this->uses('detail'),
        ]);
    }

    public function controller()
    {
        return AgentController::class;
    }
}
