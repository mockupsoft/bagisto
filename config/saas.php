<?php

return [
    'base_domain' => env('SAAS_BASE_DOMAIN', 'example.test'),

    'database' => [
        'host' => env('SAAS_TENANT_DB_HOST', '127.0.0.1'),
        'port' => env('SAAS_TENANT_DB_PORT', 3306),
        'username' => env('SAAS_TENANT_DB_USERNAME', 'root'),
        'password' => env('SAAS_TENANT_DB_PASSWORD', ''),
        'prefix' => env('SAAS_TENANT_DB_PREFIX', ''),
    ],
];
