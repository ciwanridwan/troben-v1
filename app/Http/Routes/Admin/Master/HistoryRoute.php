<?php

namespace App\Http\Routes\Admin\Master;

use App\Http\Controllers\Admin\Master\HistoryController;
use Jalameta\Router\BaseRoute;

class HistoryRoute extends BaseRoute
{
    protected $name = "admin.history";

    protected $path = "history";

    /**
     * Register routes handled by this class.
     *
     * @return void
     */
    public function register()
    {
        $this->router->get($this->prefix('paid'), [
            'as' => $this->name('paid'),
            'uses' => $this->uses('paid')
        ]);
        $this->router->get($this->prefix('cancel'), [
            'as' => $this->name('cancel'),
            'uses' => $this->uses('cancel')
        ]);
    }

    /**
     * Controller used by this route.
     *
     * @return string
     */
    public function controller()
    {
        return HistoryController::class;
    }
}
