<?php

return [
    'connections' => [
        'default' => [
            'driver' => 'rabbitmq',

            'params' => [
                'connection_class' => PhpAmqpLib\Connection\AMQPLazyConnection::class,

                'hosts' => [
                    [
                        'host' => env('RABBITMQ_HOST', '127.0.0.1'),
                        'port' => env('RABBITMQ_PORT', 5672),
                        'user' => env('RABBITMQ_USER', 'guest'),
                        'password' => env('RABBITMQ_PASSWORD', 'guest'),
                        'vhost' => env('RABBITMQ_VHOST', '/'),
                    ],
                ],

                'options' => [
                    'ssl_options' => [
                        'cafile' => env('RABBITMQ_SSL_CAFILE', null),
                        'local_cert' => env('RABBITMQ_SSL_LOCALCERT', null),
                        'local_key' => env('RABBITMQ_SSL_LOCALKEY', null),
                        'verify_peer' => env('RABBITMQ_SSL_VERIFY_PEER', true),
                        'passphrase' => env('RABBITMQ_SSL_PASSPHRASE', null),
                    ],
                ],
            ],

            'targets' => [],

            'sources' => [],
        ]
    ]
];