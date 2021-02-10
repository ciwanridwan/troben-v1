<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Log Lifetime
    |--------------------------------------------------------------------------
    |
    | Determine how long the application will retain logs information in day(s).
    */

    'log_lifetime' => 120,

    'model' => App\Auditor\AuditModel::class,

    /*
    |--------------------------------------------------------------------------
    | Audit type
    |--------------------------------------------------------------------------
    |
    | Register custom audit type, with addition to default existing types:
    | `action', `update`, `delete`, and `update`.
    */

    'audit_type' => [
        //
    ],
];
