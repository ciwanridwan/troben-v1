<?php

namespace App\Http\Routes\Api;

use App\Http\Controllers\Api\SupportController;
use App\Http\Middleware\TrustHosts;
use Jalameta\Router\BaseRoute;

class SupportRoute extends BaseRoute
{
    /**
     *
     * Registered Name.
     *
     */
    protected $prefix = '/support';

    /**
     *
     * Registered Name.
     *
     */
    protected $name = 'api.support';

    /**
     * Register routes handled by this class.
     *
     * @return void
     */
    public function register()
    {
        $this->router->group(['prefix' => $this->prefix('prospect'), 'as' => $this->name('prospect')],function () {
            $this->router->post('register', [
                'as' => '.register',
                'uses' => $this->uses('register'),
            ])->withoutMiddleware('api')->middleware(TrustHosts::class);
        });
    }

    /**
     * Controller used by this route.
     *
     * @return string
     */
    public function controller(): string
    {
        return SupportController::class;
    }
}
