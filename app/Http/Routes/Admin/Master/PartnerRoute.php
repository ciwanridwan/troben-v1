<?php

namespace App\Http\Routes\Admin\Master;

use Jalameta\Router\BaseRoute;
use App\Http\Controllers\Admin\Master\PartnerController;

class PartnerRoute extends BaseRoute
{
    /**
     * route prefix.
     *
     * @var string
     */
    protected $prefix = '/master/partner';

    /**
     * route name.
     *
     * @var string
     */
    protected $name = 'admin.master.partner';

    /**
     * Register routes handled by this class.
     *
     * @return void
     */
    public function register()
    {
        $this->router->get($this->prefix, [
            'as' => $this->name,
            'uses' => $this->uses('index'),
        ]);

        $this->router->delete($this->prefix('{hash}'), [
            'as' => $this->name('delete'),
            'uses' => $this->uses('destroy'),
        ]);
    }

    /**
     * Controller used by this route.
     *
     * @return string
     */
    public function controller()
    {
        return PartnerController::class;
    }
}
