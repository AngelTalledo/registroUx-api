<?php

declare(strict_types=1);

return [
    'settings' => [
        'displayErrorDetails' => true, // Should be set to false in production
        'logError'            => true,
        'logErrorDetails'     => true,
        'logger' => [
            'name' => 'slim-app',
            'path' => __DIR__ . '/../../logs/app.log',
            'level' => \Monolog\Level::Debug,
        ],
        'db' => [
            'driver' => 'mysql',
            'host' => 'localhost',
            'database' => 'registroux_db',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
        ],
        'jwt' => [
            'secret' => 'supersecret-key-change-it',
            'algorithm' => 'HS256',
        ],
        'uploads' => [
            'base_path' => __DIR__ . '/../../public/uploads',
            'public_url' => '/uploads',
        ],
    ],
];
