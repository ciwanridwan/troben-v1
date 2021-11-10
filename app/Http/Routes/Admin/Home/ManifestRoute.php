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
        $this->router->get($this->prefix('request/transporter'), [
            'as' => $this->name('request.transporter'),
            'uses' => $this->uses('requestTransporter')
        ]);
        $this->router->patch($this->prefix('{delivery_hash}/{partner_hash}'), [
            'as' => $this->name('partner.transporter.assign'),
            'uses' => $this->uses('assign')
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
