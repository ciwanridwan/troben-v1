<?php

namespace App\Http\Routes\Admin\Master;

use App\Http\Controllers\Admin\Master\TransporterController;
use Jalameta\Router\BaseRoute;

class TransporterRoute extends BaseRoute
{

    /**
     * @var string
     */
    protected $name = "admin.master.transporter";

    /**
     * @var string
     */
    protected $prefix = "/master/transporter";

    /**
     * Register routes handled by this class.
     *
     * @return void
     */
    public function register()
    {
        $this->router->get($this->prefix, [
            'as' => $this->name,
            'uses' => $this->uses('index')
        ]);
    }

    /**
     * Controller used by this route.
     *
     * @return string
     */
    public function controller()
    {
        return TransporterController::class;
    }
}
