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
    ],

    'web' => [
        App\Http\Routes\DefaultRoute::class,
        /** @inject web **/
    ],
    'api' => [
        App\Http\Routes\Api\GeoRoute::class,
        App\Http\Routes\Api\AuthRoute::class,
        App\Http\Routes\Api\AccountRoute::class,
        App\Http\Routes\Api\Customer\UpdateCustomerRoute::class,
        /** @inject api **/
    ],
];
