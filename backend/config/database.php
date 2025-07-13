<?php

return [
    'default' => env('DB_CONNECTION', 'mongodb'),

    'connections' => [
        'mongodb' => [
            'driver' => 'mongodb',
            'dsn' => env('DB_URI', 'homestead'),
            'database' => env('DB_DATABASE', 'forge'),
        ],
    ],

    'migrations' => 'migrations',
];
