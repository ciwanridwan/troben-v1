<?php

return [
    /**
     * server key for authorize HTTP request firebase cloud messaging
     *
     * ref:
     * https://firebase.google.com/docs/cloud-messaging/auth-server#migrate-legacy-server-keys
     */
    'serverkey' => env('FCM_SERVER_KEY'),

    'web_app' => [
        /**
         * firebase configuration for Trawlbens Web apps
         */
        'firebaseConfig' => [
            'apiKey' => env('FCM_WEB_API_KEY'),
            'authDomain' => env('FCM_WEB_AUTH_DOMAIN'),
            'projectId' => env('FCM_WEB_PROJECT_ID'),
            'storageBucket' => env('FCM_WEB_STORAGE_BUCKET'),
            'messagingSenderId' => env('FCM_WEB_MESSAGING_SENDER_ID'),
            'appId' => env('FCM_WEB_APP_ID'),
            'measurementId' => env('FCM_WEB_MEASUREMENT_ID'),
        ],

        /**
         * vapid key for fcm web interface credentials
         *
         * ref:
         * https://firebase.google.com/docs/cloud-messaging/js/client#generate_a_new_key_pair
         */
        'vapidKey' => env('FCM_WEB_VAPID_KEY'),
    ],
];
