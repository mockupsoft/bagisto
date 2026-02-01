<?php

namespace App\Services\Tenant;

use App\Models\Tenant\TenantDatabase;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;

class TenantConnectionConfigurator
{
    public function configure(TenantDatabase $db): void
    {
        if (empty($db->database_name)) {
            throw new \InvalidArgumentException('Database name cannot be empty for tenant ID: ' . $db->tenant_id);
        }

        $default = Config::get('database.connections.mysql', []);

        // Ensure password is not null (empty string is OK, but null causes issues)
        $password = $db->database_password;
        if ($password === null) {
            $password = '';
        }

        $connection = [
            'driver' => 'mysql',
            'host' => $db->database_host ?? '127.0.0.1',
            'port' => $db->database_port ?? 3306,
            'database' => $db->database_name,
            'username' => $db->database_username ?? 'root',
            'password' => $password,
            'prefix' => $db->database_prefix ?? '',
            'charset' => Arr::get($default, 'charset', 'utf8mb4'),
            'collation' => Arr::get($default, 'collation', 'utf8mb4_unicode_ci'),
            'strict' => Arr::get($default, 'strict', true),
            'engine' => Arr::get($default, 'engine', null),
        ];

        Config::set('database.connections.tenant', $connection);
    }
}
