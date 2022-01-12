<?php

return [
    /*
     * Default Bot is JobBot declare on env with key TELEGRAM_BOT_TOKEN
     * storing at config.services.telegram-bot-api
     */
    'bot' => [
        'jon_bot_token' => env('TELEGRAM_BOT_TOKEN','2122704563:AAHa9ovq8fAez4gmxqrlQzQfOadkfJdbtrk'),
        'ray_bot_token' => env('TELEGRAM_RAY_BOT_TOKEN','2122704563:AAHa9ovq8fAez4gmxqrlQzQfOadkfJdbtrk'),
    ],
    'chat' => [
        'app_group' => env('TELEGRAM_APP_GROUP_ID', -1001404809797),
        'finance_group' => env('TELEGRAM_FINANCE_GROUP_ID', -1001404809797),
        'new_partner_group' => env('TELEGRAM_NEW_PARTNER_GROUP_ID', -1001404809797)
    ],
];
