<?php

/**
 * Router configuration.
 *
 * @author      veelasky <veelasky@gmail.com>
 */

return [
    'groups' => [
        'api' => [
            'middleware' => ['api'],
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
        'partner' => [
            'middleware' => ['web', 'auth', 'partner.role:cashier,customer-service', 'partner.scope.role:cashier,customer-service'],
            'prefix' => 'partner',
        ],
        'cashier' => [
            'middleware' => ['web', 'auth', 'partner.role:cashier', 'partner.scope.role:cashier'],
            'prefix' => 'partner/cashier',
        ],
        'customer_service' => [
            'middleware' => ['web', 'auth', 'partner.role:customer-service', 'partner.scope.role:customer-service'],
            'prefix' => 'partner/customer-service',
        ],
        'self_service' => [
            'middleware' => ['api'],
            'prefix' => empty(env('API_DOMAIN')) ? 'api/v/sf' : 'v/sf',
            'domain' => env('API_DOMAIN')
        ],
        'operation' => [
            'middleware' => ['api'],
            'prefix' => empty(env('API_DOMAIN')) ? 'api/operation' : 'operation',
            'domain' => env('API_DOMAIN')
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
        App\Http\Routes\Api\Internal\FinanceRoute::class,
        App\Http\Routes\Api\Internal\WithdrawalRoute::class,
        App\Http\Routes\Api\Partner\AssetRoute::class,
        App\Http\Routes\Api\Partner\PartnerRoute::class,
        App\Http\Routes\Api\Partner\VoucherRoute::class,
        App\Http\Routes\Api\OrderRoute::class,
        App\Http\Routes\Api\HandlingRoute::class,
        App\Http\Routes\Api\Partner\Warehouse\OrderRoute::class,
        App\Http\Routes\Api\Partner\Driver\OrderRoute::class,
        App\Http\Routes\Api\Partner\Driver\Order\PickupRoute::class,
        App\Http\Routes\Api\Partner\Owner\OrderRoute::class,
        App\Http\Routes\Api\Partner\Owner\WithdrawalRoute::class,
        App\Http\Routes\Api\Partner\Warehouse\Order\ItemRoute::class,
        App\Http\Routes\Api\Partner\Warehouse\ManifestRoute::class,
        App\Http\Routes\Api\Partner\Driver\Order\TransitRoute::class,
        App\Http\Routes\Api\Partner\Warehouse\Manifest\TransitRoute::class,
        App\Http\Routes\Api\Partner\Driver\Order\DooringRoute::class,
        App\Http\Routes\Api\Partner\Warehouse\DooringRoute::class,
        App\Http\Routes\Api\Payment\NicepayRoute::class,
        App\Http\Routes\Api\PaymentRoute::class,
        App\Http\Routes\Api\VersionRoute::class,
        App\Http\Routes\Api\PromoRoute::class,
        App\Http\Routes\Api\Partner\Owner\BalanceRoute::class,
        App\Http\Routes\Api\NotificationRoute::class,
        App\Http\Routes\Api\Partner\Owner\ScheduleTransportationRoute::class,
        App\Http\Routes\Api\SupportRoute::class,
        App\Http\Routes\TermAndConditionRoute::class,
        App\Http\Routes\Api\MotorBikeRoute::class,
        App\Http\Routes\TestingRoute::class,
        App\Http\Routes\Api\Internal\ManifestRoute::class,
        App\Http\Routes\Api\Partner\Owner\CheckRoute::class,
        App\Http\Routes\Api\V2\Dashboard\OrderRoute::class,
        App\Http\Routes\Api\Partner\DashboardOwnerRoute::class,
        App\Http\Routes\Api\Partner\Cashier\OrderRoute::class,
        App\Http\Routes\Api\Order\FinishedRoute::class
        /** @inject api **/
    ],
    'admin' => [
        App\Http\Routes\Admin\MasterRoute::class,
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
        App\Http\Routes\Admin\Master\Withdraw\DetailRequestRoute::class,
        App\Http\Routes\Admin\Home\ManifestRoute::class,
        App\Http\Routes\Admin\GeoRoute::class,
        App\Http\Routes\Admin\AdminRoute::class,
        App\Http\Routes\Admin\Master\AccountExecutive\AgentRoute::class,
        App\Http\Routes\Admin\Master\Trawltruck\DashboardRoute::class,
        # removed at 02-Jan-2021 because not use
        // App\Http\Routes\Admin\Payment\MPWRouter::class,
        // App\Http\Routes\Admin\Payment\MTAKabRouter::class,
        // App\Http\Routes\Admin\Payment\MTAKRouter::class,
        /** @inject admin **/
    ],
    'cashier' => [
        App\Http\Routes\Partner\Cashier\HomeRoute::class,
        App\Http\Routes\Partner\Cashier\Home\WaitingRoute::class,
        /** @inject cashier **/
    ],
    'customer_service' => [
        App\Http\Routes\Partner\CustomerService\HomeRoute::class,
        App\Http\Routes\Partner\CustomerService\Home\OrderRoute::class,
        App\Http\Routes\Partner\CustomerService\Home\WaitingRoute::class,
        App\Http\Routes\Partner\CustomerService\Home\Order\WalkinRoute::class,
        App\Http\Routes\Partner\CustomerService\CustomerServiceRoute::class,
        /** @inject customer_service **/
    ],
    'partner' => [
        App\Http\Routes\Partner\PartnerRoute::class,
        /** @inject partner **/
    ],
    'self_service' => [
        App\Http\Routes\Api\V\SelfRoute::class,
        App\Http\Routes\Api\V\OfficeRoute::class,
        App\Http\Routes\Api\SlaRoute::class,

    ],

    'operation' => [
        App\Http\Routes\Api\Operation\PackageRoute::class
    ]
];
