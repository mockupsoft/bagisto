<?php

return [
    'base_domain' => env('SAAS_BASE_DOMAIN', 'example.test'),

    'reserved_subdomains' => [
        'admin',
        'api',
        'www',
        'root',
        'support',
        'mail',
        'billing',
    ],

    'tenant_db' => [
        'name_prefix' => env('SAAS_TENANT_DB_NAME_PREFIX', 'tenant_'),
        'host' => env('SAAS_TENANT_DB_HOST', '127.0.0.1'),
        'port' => env('SAAS_TENANT_DB_PORT', 3306),
        'username' => env('SAAS_TENANT_DB_USERNAME', 'root'),
        'password' => env('SAAS_TENANT_DB_PASSWORD', ''),
        'prefix' => env('SAAS_TENANT_DB_PREFIX', ''),
        'provisioning_enabled' => env('SAAS_TENANT_DB_PROVISIONING_ENABLED', false),
        'seed_enabled' => env('SAAS_TENANT_DB_SEED_ENABLED', false),
        'connection_template' => env('SAAS_TENANT_DB_CONNECTION_TEMPLATE', 'mysql'),
        'migrations_path' => env('SAAS_TENANT_DB_MIGRATIONS_PATH', 'database/migrations/tenant'),
        'charset' => env('SAAS_TENANT_DB_CHARSET', 'utf8mb4'),
        'collation' => env('SAAS_TENANT_DB_COLLATION', 'utf8mb4_unicode_ci'),
    ],
];
