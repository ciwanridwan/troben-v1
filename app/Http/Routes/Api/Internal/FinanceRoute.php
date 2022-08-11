<?php

namespace App\Http\Routes\Api\Internal;

use App\Http\Controllers\Api\Internal\FinanceController;
use Jalameta\Router\BaseRoute;

class FinanceRoute extends BaseRoute
{
    /**
     * Route path prefix.
     *
     * @var string
     */
    protected $prefix = '/internal/finance';

    /**
     * Registered route name.
     *
     * @var string
     */
    protected $name = 'api.internal.finance';

    /**
     * Register routes handled by this class.
     *
     * @return void
     */
    public function register()
    {
        /**Move to request route */
    }

    /**
     * Controller used by this route.
     *
     * @return string
     */
    public function controller()
    {
        return FinanceController::class;
    }
}
