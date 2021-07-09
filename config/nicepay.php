<?php

use App\Models\Payments;

return [
    /*
     * base url api nicepay.
     */
    'uri' => 'https://www.nicepay.co.id/nicepay/direct/v2/',

    /*
     * endpoint registration.
     */
    'registration_url' => 'registration',
    'cancel_url' => 'cancel',

    /*
     * endpoint inquiry.
     */
    'inquiry_url' => 'inquiry',

    /*
     * webhook url handle notification.
     */
    'db_process_url' => env('API_DOMAIN', config('app.url').'/api').'/payment/nicepay/webhook',

    /*
     * imid from nicepay.
     */
    'imid' => env('NICEPAY_IMID', 'IONPAYTEST'),

    /*
     * merchant key from nicepay.
     */
    'merchant_key' => env('NICEPAY_MERCHANT_KEY', '33F49GnCMS1mFYlGXisbUDzVf2ATWCl9k3R++d5hDd3Frmuos/XLx8XhXpe+LDYAbpGKZYSwtlyyLOtS/8aD7A=='),

    /*
     * mitra code from nicepay.
     */
    'mitra_code' => env('NICEPAY_MITRA_CODE', 'QSHP'),

    /*
     * shop id from nicepay.
     */
    'shop_id' => env('NICEPAY_SHOP_ID', 'NICEPAY'),

    /*
     * merchant fix account id from nicepay.
     */
    'merchant_fix_account_id' => env('NICEPAY_MERCHANT_FIX_ACCOUNT_ID', ''),

    /*
     * list code for payment method from nicepay.
     */
    'payment_method_code' => [
        'va' => '02',
        'qris' => '08',
    ],

    /*
     * list code for bank from nicepay.
     */
    'bank_code' => [
        Payments\Gateway::CHANNEL_NICEPAY_BCA_VA => 'CENA',
        Payments\Gateway::CHANNEL_NICEPAY_MANDIRI_VA => 'BMRI',
        Payments\Gateway::CHANNEL_NICEPAY_PERMATA_VA => 'BBBA',
        Payments\Gateway::CHANNEL_NICEPAY_BRI_VA => 'BRIN',
        Payments\Gateway::CHANNEL_NICEPAY_BNI_VA => 'BNIN',
        Payments\Gateway::CHANNEL_NICEPAY_CIMB_VA => 'BNIA',

    ]
];
