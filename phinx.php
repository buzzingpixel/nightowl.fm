<?php

declare(strict_types=1);

return [
    'paths' => [
        'migrations' => '%%PHINX_CONFIG_DIR%%/config/Data/Migrations',
        'seeds' => '%%PHINX_CONFIG_DIR%%/config/Data/Seeds',
    ],
    'environments' => [
        'default_migration_table' => 'migrations',
        'default_database' => 'production',
        'production' => [
            'adapter' => 'pgsql',
            'host' => 'db',
            'name' => 'nightowl',
            'user' => getenv('DB_USER'),
            'pass' => getenv('DB_PASSWORD'),
            'port' => '5432',
        ],
    ],
    'version_order' => 'creation',
];
