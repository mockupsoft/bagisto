<?php

namespace Tests\Support;

use App\Models\Tenant\Tenant;
use App\Models\Tenant\TenantDatabase;
use App\Support\Tenant\TenantContext;
use App\Services\Tenant\TenantConnectionConfigurator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class TenantTestContext
{
    public static function setTenantContext(Tenant $tenant, TenantDatabase $tenantDb): void
    {
        $context = app(TenantContext::class);
        $context->setTenant($tenant);
        app()->instance(TenantContext::class, $context);

        // Configure tenant connection
        app(TenantConnectionConfigurator::class)->configure($tenantDb);

        // IMPORTANT: Reset cached connection to ensure database parameter is updated
        DB::purge('tenant');
        DB::reconnect('tenant');
    }

    public static function clearTenantContext(): void
    {
        if (app()->bound(TenantContext::class)) {
            app(TenantContext::class)->clear();
        }

        // Purge tenant connection to prevent test pollution
        DB::purge('tenant');
    }

    /**
     * Reset tenant database (drop all tables and re-run migrations + seed).
     */
    public static function resetTenantDatabase(TenantDatabase $tenantDb): void
    {
        // Configure tenant connection
        app(TenantConnectionConfigurator::class)->configure($tenantDb);
        DB::purge('tenant');
        DB::reconnect('tenant');

        // Disable foreign key checks
        DB::connection('tenant')->statement('SET FOREIGN_KEY_CHECKS=0');

        // Get all table names
        $tables = DB::connection('tenant')->select('SHOW TABLES');
        $tableKey = 'Tables_in_' . $tenantDb->database_name;

        // Drop all tables
        foreach ($tables as $table) {
            $tableName = $table->$tableKey;
            DB::connection('tenant')->statement("DROP TABLE IF EXISTS `{$tableName}`");
        }

        // Re-enable foreign key checks
        DB::connection('tenant')->statement('SET FOREIGN_KEY_CHECKS=1');

        // Re-run migrations
        Artisan::call('migrate', [
            '--database' => 'tenant',
            '--path' => config('saas.tenant_db.migrations_path', 'database/migrations/tenant'),
            '--force' => true,
        ]);

        // Re-seed catalog
        if (config('saas.tenant_db.seed_enabled', false)) {
            try {
                app(\App\Services\Tenant\TenantCatalogSeeder::class)->seed();
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }
}
