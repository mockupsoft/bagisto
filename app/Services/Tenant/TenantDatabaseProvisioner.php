<?php

namespace App\Services\Tenant;

use App\Models\Tenant\Tenant;
use App\Models\Tenant\TenantDatabase;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Throwable;

class TenantDatabaseProvisioner
{
    public function __construct(
        protected TenantConnectionConfigurator $configurator,
    ) {
    }

    /**
     * Provision by tenant id.
     */
    public function provisionByTenantId(int $tenantId): array
    {
        $tenantDb = TenantDatabase::where('tenant_id', $tenantId)
            ->whereNull('deleted_at')
            ->first();

        if (! $tenantDb) {
            return ['ok' => false, 'reason' => 'tenant_db_missing'];
        }

        return $this->provision($tenantDb);
    }

    /**
     * Provision a tenant database (create DB, run migrations, optional seed).
     */
    public function provision(TenantDatabase $tenantDb): array
    {
        $config = Config::get('saas.tenant_db', []);

        if (! ($config['provisioning_enabled'] ?? false)) {
            return ['ok' => false, 'reason' => 'disabled'];
        }

        if (empty($tenantDb->database_name)) {
            return ['ok' => false, 'reason' => 'invalid_state'];
        }

        $tenant = Tenant::where('id', $tenantDb->tenant_id)->first();

        if (! $tenant || $tenant->status !== 'active') {
            return ['ok' => false, 'reason' => 'invalid_state'];
        }

        $templateConnection = $config['connection_template'] ?? 'mysql';
        $charset = $config['charset'] ?? 'utf8mb4';
        $collation = $config['collation'] ?? 'utf8mb4_unicode_ci';

        $dbName = $this->escapeIdentifier($tenantDb->database_name);

        try {
            // Create database if not exists using template connection.
            $createSql = sprintf(
                'CREATE DATABASE IF NOT EXISTS `%s` CHARACTER SET %s COLLATE %s',
                $dbName,
                $charset,
                $collation
            );

            DB::connection($templateConnection)->statement($createSql);

            // Configure tenant connection runtime.
            $this->configurator->configure($tenantDb);

            // Run tenant migrations.
            Artisan::call('migrate', [
                '--database' => 'tenant',
                '--path' => $config['migrations_path'] ?? 'database/migrations/tenant',
                '--force' => true,
            ]);

            // Optional seed: insert provisioned_at meta.
            if ($config['seed_enabled'] ?? false) {
                DB::connection('tenant')->table('tenant_meta')->updateOrInsert(
                    ['key' => 'provisioned_at'],
                    ['value' => now()->toIso8601String(), 'updated_at' => now(), 'created_at' => now()]
                );
            }
        } catch (Throwable $e) {
            return ['ok' => false, 'reason' => $this->truncateReason($e->getMessage())];
        }

        return ['ok' => true, 'reason' => null];
    }

    protected function escapeIdentifier(string $name): string
    {
        return str_replace('`', '``', $name);
    }

    protected function truncateReason(?string $reason): ?string
    {
        if (is_null($reason)) {
            return null;
        }

        return mb_strimwidth($reason, 0, 1000);
    }
}
