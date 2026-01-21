<?php

namespace Tests\Support;

use App\Models\Tenant\Tenant;
use App\Models\Tenant\TenantDatabase;
use App\Support\Tenant\TenantContext;
use App\Services\Tenant\TenantConnectionConfigurator;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class TenantTestContext
{
    public static function setTenantContext(Tenant $tenant, TenantDatabase $tenantDb): void
    {
        $context = app(TenantContext::class);
        $context->setTenant($tenant);

        app(TenantConnectionConfigurator::class)->configure($tenantDb);

        DB::purge('tenant');
        DB::reconnect('tenant');
    }

    public static function resetTenantDatabase(TenantDatabase $tenantDb): void
    {
        app(TenantConnectionConfigurator::class)->configure($tenantDb);

        DB::purge('tenant');
        DB::reconnect('tenant');

        $dbName = $tenantDb->database_name;
        $template = config('saas.tenant_db.connection_template', 'mysql');
        $templateConn = DB::connection($template);

        $escapedDbName = str_replace('`', '``', $dbName);
        $templateConn->statement('DROP DATABASE IF EXISTS `' . $escapedDbName . '`');

        $charset = config('saas.tenant_db.charset', 'utf8mb4');
        $collation = config('saas.tenant_db.collation', 'utf8mb4_unicode_ci');
        $templateConn->statement('CREATE DATABASE `' . $escapedDbName . '` CHARACTER SET ' . $charset . ' COLLATE ' . $collation);


        app(TenantConnectionConfigurator::class)->configure($tenantDb);
        DB::purge('tenant');
        DB::reconnect('tenant');

        Artisan::call('migrate', [
            '--database' => 'tenant',
            '--path' => config('saas.tenant_db.migrations_path', 'database/migrations/tenant'),
            '--force' => true,
        ]);
    }

    public static function clearTenantContext(): void
    {
        if (app()->bound(TenantContext::class)) {
            app(TenantContext::class)->clear();
        }

        DB::purge('tenant');
    }
}
