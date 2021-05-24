<?php

namespace App\Http\Routes\Admin\Home;

use App\Http\Controllers\Admin\Home\ManifestController;
use Jalameta\Router\BaseRoute;

class ManifestRoute extends BaseRoute
{
    /**
     * @var string
     */
    protected $name = 'admin.home.manifest';

    /**
     * @var string
     */
    protected $prefix = 'home/manifest';


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
        // make an awesome route
    }

    /**
     * Controller used by this route.
     *
     * @return string
     */
    public function controller()
    {
        return ManifestController::class;
    }
}
