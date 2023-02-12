<?php

namespace App\Http\Routes\Api\Internal;

use App\Http\Controllers\Api\Internal\ManifestController;
use Jalameta\Router\BaseRoute;

class ManifestRoute extends BaseRoute
{
    /**
     * @var string
     */
    protected $name = 'ho.manifest';

    /**
     * @var string
     */
    protected $prefix = 'fno/tracking';

    /**
     * Register routes handled by this class.
     *
     * @return void
     */
    public function register()
    {
        // make an awesome route
        $this->router->get($this->prefix('manifest'), [
            'as' => $this->name('index'),
            'uses' => $this->uses('index')
        ]);
        $this->router->get($this->prefix('request/transporter'), [
            'as' => $this->name('fno.request.transporter'),
            'uses' => $this->uses('requestTransporter')
        ]);
        $this->router->patch($this->prefix('assign/{delivery_hash}/{partner_hash}'), [
            'as' => $this->name('fno.partner.transporter.assign'),
            'uses' => $this->uses('assign')
        ]);
    }

    public function controller()
    {
        return ManifestController::class;
    }
}
