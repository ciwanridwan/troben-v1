<?php

namespace App\Http\Routes\Api\Auth;

use Jalameta\Router\BaseRoute;
use App\Http\Controllers\Api\Auth\OtpController;

class OtpRoute extends BaseRoute
{
    /**
     * @var string
     */
    protected $prefix = '/auth/otp';

    /**
     * @var string
     */
    protected $name = 'api.auth.otp';

    /**
     * Register routes handled by this class.
     *
     * @return void
     */
    public function register()
    {
        $this->router->post($this->prefix('verify'), [
            'as' => $this->name('verify'),
            'uses' => $this->uses('verifyToken'),
        ])->withoutMiddleware('api');
    }

    /**
     * Controller used by this route.
     *
     * @return string
     */
    public function controller()
    {
        return OtpController::class;
    }
}
