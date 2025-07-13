<?php

return [
    'trackimo' => [
        'base_url' => env('TRACKIMO_BASEURL', 'https://app.trackimo.com'),
        'user_name' => env('TRACKIMO_USERNAME'),
        'password' => env('TRACKIMO_PASSWORD'),
        'client_id' => env('TRACKIMO_CLIENT_ID'),
        'client_secret' => env('TRACKIMO_CLIENT_SECRET'),
        'redirect_site' => env('TRACKIMO_REDIRECT_URL')
    ],

    'baremail' => [
        'base_url' => env('BAREMAIL_BASEURL'),
        'user_id' => env('BAREMAIL_USER_ID'),
        'password' => env('BAREMAIL_PASSWORD'),
        'site_id' => env('BAREMAIL_SITE_ID'),
        'service_id' => env('BAREMAIL_SERVICE_ID'),
        'sl_no' => env('BAREMAIL_SL_NO'),
    ]
];
