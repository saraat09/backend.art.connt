<?php

return [

     'default' => 'log',
    'connections' => [

        'pusher' => [
            'driver'  => 'pusher',
            'key'     => 'dummy', // env('PUSHER_APP_KEY'),
            'secret'  => 'dummy', // env('PUSHER_APP_SECRET'),
            'app_id'  => 'dummy', // env('PUSHER_APP_ID'),
            'options' => [
                'cluster'   => env('PUSHER_APP_CLUSTER', 'eu'),
                'encrypted' => true,
                'host'      => env('PUSHER_HOST') ?: 'api-' . env('PUSHER_APP_CLUSTER', 'eu') . '.pusher.com',
                'port'      => env('PUSHER_PORT', 443),
                'scheme'    => env('PUSHER_SCHEME', 'https'),
                'useTLS'    => true,
            ],
            'client_options' => [],
        ],

        'log'  => ['driver' => 'log'],
        'null' => ['driver' => 'null'],
    ],

];
