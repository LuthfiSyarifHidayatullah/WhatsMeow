<?php

return [
    'bot' => [
        'token' => env('BOT_API_TOKEN', 'default-bot-secret-token'),
        'webhook_url' => env('BOT_WEBHOOK_URL', 'http://localhost:8080'),
    ],

    'pusher' => [
        'app_id' => env('PUSHER_APP_ID'),
        'app_key' => env('PUSHER_APP_KEY'),
        'app_secret' => env('PUSHER_APP_SECRET'),
        'app_cluster' => env('PUSHER_APP_CLUSTER'),
    ],
];
