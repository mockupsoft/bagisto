<?php

namespace App\Services\Tenant;

use App\Models\Tenant\TenantDatabase;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;

class TenantConnectionConfigurator
{
    public function configure(TenantDatabase $db): void
    {
        $default = Config::get('database.connections.mysql', []);

        $connection = [
            'driver' => 'mysql',
            'host' => $db->database_host,
            'port' => $db->database_port,
            'database' => $db->database_name,
            'username' => $db->database_username,
            'password' => $db->database_password,
            'prefix' => $db->database_prefix ?? '',
            'charset' => Arr::get($default, 'charset', 'utf8mb4'),
            'collation' => Arr::get($default, 'collation', 'utf8mb4_unicode_ci'),
            'strict' => Arr::get($default, 'strict', true),
            'engine' => Arr::get($default, 'engine', null),
        ];

        Config::set('database.connections.tenant', $connection);
    }
}
