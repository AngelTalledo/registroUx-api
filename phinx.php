<?php

$settings = require __DIR__ . '/app/config/settings.php';
$db = $settings['settings']['db'];

return [
    'paths' => [
        'migrations' => '%%PHINX_CONFIG_DIR%%/db/migrations',
        'seeds' => '%%PHINX_CONFIG_DIR%%/db/seeds'
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_environment' => 'development',
        'development' => [
            'adapter' => 'mysql',
            'host' => $db['host'],
            'name' => $db['database'],
            'user' => $db['username'],
            'pass' => $db['password'],
            'port' => '3306',
            'charset' => $db['charset'],
        ]
    ],
    'version_order' => 'creation'
];
