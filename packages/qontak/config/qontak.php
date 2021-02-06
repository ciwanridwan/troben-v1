<?php

return [
    /**
     * Qontak base url.
     */
    'base_url' => env('QONTAK_BASE_URL', 'https://chat-service.qontak.com/'),


    /**
     * Qontak API Hub Credentials.
     */
    'username' => env('QONTAK_USERNAME'),
    'password' => env('QONTAK_PASSWORD'),
    'client_id' => env('QONTAK_CLIENT_ID'),
    'client_secret' => env('QONTAK_CLIENT_SECRET'),

    /**
     * WhatsApp Template Factory.
     */
    'templates' => [
        // list of all registered templates.
    ],
];
