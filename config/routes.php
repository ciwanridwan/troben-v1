<?php

/**
         * Router configuration.
 *
 * @author      veelasky <veelasky@gmail.com>
 */

return [
    'groups' => [
        'api' => [
            'middleware' => ['api', 'auth:sanctum'],
            'prefix' => empty(env('API_DOMAIN')) ? 'api' : '',
            'domain' => env('API_DOMAIN')
        ],
        'web' => [
            'middleware' => 'web',
            'prefix' => '',
        ],
        'admin' => [
            'middleware' => ['web', 'auth', 'is.admin'],
            'prefix' => 'admin',
        ],
        'cashier' => [
            'middleware' => ['web', 'auth', 'partner.role:cashier', 'partner.scope.role:cashier'],
            'prefix' => 'partner/cashier',
        ],
        'customer_service' => [
            'middleware' => ['web', 'auth', 'partner.role:customer-service', 'partner.scope.role:customer-service'],
            'prefix' => 'partner/customer-service',
        ]

    ],

    'web' => [
        App\Http\Routes\DefaultRoute::class,
        App\Http\Routes\FortifyRoute::class,
        /** @inject web **/

        App\Http\Routes\VariableBindingRoute::class,
    ],
    'api' => [
        App\Http\Routes\Api\Auth\AuthRoute::class,
        App\Http\Routes\Api\Auth\OtpRoute::class,
        App\Http\Routes\Api\GeoRoute::class,
        App\Http\Routes\Api\AccountRoute::class,
        App\Http\Routes\Api\PricingRoute::class,
        App\Http\Routes\Api\ServiceRoute::class,
        App\Http\Routes\Api\ProductRoute::class,
        App\Http\Routes\Api\TransporterRoute::class,
        App\Http\Routes\Api\Partner\AssetRoute::class,
        App\Http\Routes\Api\OrderRoute::class,
        App\Http\Routes\Api\HandlingRoute::class,
        App\Http\Routes\Api\Partner\Warehouse\OrderRoute::class,
        \App\Http\Routes\Api\Partner\Driver\OrderRoute::class,
        App\Http\Routes\Api\Partner\Driver\Order\PickupRoute::class,
        App\Http\Routes\Api\Partner\Owner\OrderRoute::class,
        App\Http\Routes\Api\Partner\Warehouse\Order\ItemRoute::class,
        App\Http\Routes\Api\Partner\Warehouse\ManifestRoute::class,
        /** @inject api **/
    ],
    'admin' => [
        App\Http\Routes\Admin\HomeRoute::class,
        App\Http\Routes\Admin\Master\CustomerRoute::class,
        App\Http\Routes\Admin\Master\PartnerRoute::class,
        App\Http\Routes\Admin\Master\PricingRoute::class,
        App\Http\Routes\Admin\Master\EmployeeRoute::class,
        App\Http\Routes\Admin\Master\TransporterRoute::class,
        App\Http\Routes\Admin\Master\PaymentRoute::class,
        App\Http\Routes\Admin\Master\HistoryRoute::class,
        App\Http\Routes\Admin\Master\Withdraw\RequestRoute::class,
        App\Http\Routes\Admin\Master\Withdraw\PendingRoute::class,
        App\Http\Routes\Admin\Master\Withdraw\SuccessRoute::class,
        /** @inject admin **/
    ],
    'cashier' => [
        App\Http\Routes\Partner\Cashier\HomeRoute::class,
        App\Http\Routes\Partner\Cashier\Home\WaitingRoute::class,
        /** @inject cashier **/
    ],
    'customer_service' => [
        App\Http\Routes\Partner\CustomerService\HomeRoute::class,
        App\Http\Routes\Partner\CustomerService\OrderRoute::class,
        /** @inject customer_service **/
    ],

];
