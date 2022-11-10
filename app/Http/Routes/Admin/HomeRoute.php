<?php

namespace App\Http\Routes\Admin;

use Jalameta\Router\BaseRoute;
use App\Http\Controllers\Admin\HomeController;

class HomeRoute extends BaseRoute
{
    /**
     * @var string
     */
    protected $prefix = 'home';

    /**
     * @var string
     */
    protected $name = 'admin.home';


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
        $this->router->get($this->prefix('receipt'), [
            'as' => $this->name('receipt'),
            'uses' => $this->uses('receipt'),
        ]);

        $this->router->get($this->prefix('accountexecutive'), [
            'as' => $this->name('accountexecutive'),
            'uses' => $this->uses('accountExecutive'), //ini manggil home controller
        ]);

        $this->router->get($this->prefix('accountexecutive/teamagent'), [
            'as' => $this->name('accountexecutive.teamagent'),
            'uses' => $this->uses('teamAgent'),
        ]);

        $this->router->get($this->prefix('accountexecutive/teamdetail/{code}'), [
            'as' => $this->name('accountexecutive.teamdetail'),
            'uses' => $this->uses('teamDetail'),
        ]);

        $this->router->get($this->prefix('form-register/trawlbens-corporate'), [
            'as' => $this->name('formregister.trawlbenscorporate'),
            'uses' => $this->uses('trawlbensCorporate'),
        ]);

        $this->router->get($this->prefix('form-register/mitra-bisnis'), [
            'as' => $this->name('formregister.mitrabisnis'),
            'uses' => $this->uses('mitraBisnis'),
        ]);

        $this->router->get($this->prefix('form-register/mitra-space'), [
            'as' => $this->name('formregister.mitraspace'),
            'uses' => $this->uses('mitraSpace'),
        ]);

        $this->router->get($this->prefix('form-register/mitra-pos'), [
            'as' => $this->name('formregister.mitrapos'),
            'uses' => $this->uses('mitraPos'),
        ]);

        $this->router->get($this->prefix('form-register/mitra-pool-warehouse'), [
            'as' => $this->name('formregister.mitrapoolwarehouse'),
            'uses' => $this->uses('mitraPoolWarehouse'),
        ]);

        $this->router->get($this->prefix('form-register/mitra-kurir-motor'), [
            'as' => $this->name('formregister.mitrakurirmotor'),
            'uses' => $this->uses('mitraKurirMotor'),
        ]);

        $this->router->get($this->prefix('form-register/mitra-kurir-mobil'), [
            'as' => $this->name('formregister.mitrakurirmobil'),
            'uses' => $this->uses('mitraKurirMobil'),
        ]);

        $this->router->post($this->prefix('receipt/{package_hash}'), [
            'as' => $this->name('receipt.log.store'),
            'uses' => $this->uses('storeLog'),
        ]);

        $this->router->patch($this->prefix('{package_hash}/{partner_hash}/assign'), [
            'as' => $this->name('assign'),
            'uses' => $this->uses('orderAssignation'),
        ]);
        $this->router->patch($this->prefix('{package_hash}/payment-confirm'), [
            'as' => $this->name('paymentConfirm'),
            'uses' => $this->uses('paymentConfirm'),
        ]);
        $this->router->patch($this->prefix('{package_hash}/cancel'), [
            'as' => $this->name('cancel'),
            'uses' => $this->uses('cancel'),
        ]);

        $this->router->get($this->prefix('loginother'), [
            'as' => $this->name('loginother'),
            'uses' => $this->uses('loginother'),
        ]);
        $this->router->post($this->prefix('loginother'), [
            'as' => $this->name('loginotherSubmit'),
            'uses' => $this->uses('loginotherSubmit'),
        ]);
    }

    /**
     * Controller used by this route.
     *
     * @return string
     */
    public function controller()
    {
        return HomeController::class;
    }
}
