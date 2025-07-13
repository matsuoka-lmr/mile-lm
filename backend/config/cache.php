<?php

return [
    'default' => env('CACHE_STORE', 'mongodb'),
    'stores' => [
        'mongodb' => [
            'driver' => 'mongodb',
            'connection' => 'mongodb',
            'events' => false,
        ],
    ],
];
