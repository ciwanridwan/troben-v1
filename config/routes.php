<?php

/**
         * Router configuration.
 *
 * @author      veelasky <veelasky@gmail.com>
 */

return [
    'groups' => [
        'web' => [
            'middleware' => 'web',
            'prefix' => '',
        ],
        'api' => [
            'middleware' => 'api',
            'prefix' => empty(env('API_DOMAIN')) ? 'api' : '',
            'domain' => env('API_DOMAIN')
        ],

        'admin' => [
            'middleware' => ['web', 'auth'],
            'prefix' => 'admin',
        ],
    ],

    'web' => [
        App\Http\Routes\DefaultRoute::class,
        App\Http\Routes\FortifyRoute::class,
        /** @inject web **/
    ],
    'api' => [
        App\Http\Routes\Api\Auth\AuthRoute::class,
        App\Http\Routes\Api\Auth\OtpRoute::class,
        App\Http\Routes\Api\GeoRoute::class,
        App\Http\Routes\Api\AccountRoute::class,
        App\Http\Routes\Api\PricingRoute::class,
        App\Http\Routes\Api\ServiceRoute::class,
        /** @inject api **/
    ],
    'admin' => [
        App\Http\Routes\Admin\DashboardRoute::class,
        /** @inject admin **/
    ],
];
