<?php

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

return [
    'default' => env('LOG_CHANNEL', 'stack'),

    'channels' => [
        'stack' => [
            'driver' => 'stack',
            'channels' => ['single'],
            'ignore_exceptions' => false,
        ],

        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/lumen.log'),
            'level' => env('LOG_LEVEL', 'debug'), // ここをdebugに設定
        ],

        // 必要に応じて他のチャネルを追加
    ],
];