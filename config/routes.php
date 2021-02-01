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
            'prefix' => 'api',
        ],
    ],

    'web' => [
        App\Http\Routes\DefaultRoute::class,
        /** @inject web **/
    ],
    'api' => [
        App\Http\Routes\Api\GeoRoute::class,
        /** @inject api **/
    ],
];
