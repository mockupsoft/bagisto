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
    public function provisionByTenantId(int $tenantId, array $options = []): array
    {
        $tenantDb = TenantDatabase::where('tenant_id', $tenantId)
            ->whereNull('deleted_at')
            ->first();

        if (! $tenantDb) {
            return ['ok' => false, 'reason' => 'tenant_db_missing'];
        }

        return $this->provision($tenantDb, $options);
    }

    /**
     * Provision a tenant database (create DB, run migrations, optional seed).
     */
    public function provision(TenantDatabase $tenantDb, array $options = []): array
    {
        $config = Config::get('saas.tenant_db', []);

        $enabled = ($options['force_enable'] ?? false) || ($config['provisioning_enabled'] ?? false);

        if (! $enabled) {
            return ['ok' => false, 'reason' => 'disabled'];
        }

        if (empty($tenantDb->database_name)) {
            $tenantDb->status = 'failed';
            $tenantDb->last_error = 'invalid_state';
            $tenantDb->save();

            return ['ok' => false, 'reason' => 'invalid_state'];
        }

        $tenant = Tenant::where('id', $tenantDb->tenant_id)->first();

        if (! $tenant || $tenant->status !== 'active') {
            $tenantDb->status = 'failed';
            $tenantDb->last_error = 'invalid_state';
            $tenantDb->save();

            return ['ok' => false, 'reason' => 'invalid_state'];
        }

        $seedOverride = $options['seed'] ?? null;
        $seed = is_null($seedOverride)
            ? ($config['seed_enabled'] ?? false)
            : (bool) $seedOverride;

        $templateConnection = $config['connection_template'] ?? 'mysql';
        $charset = $config['charset'] ?? 'utf8mb4';
        $collation = $config['collation'] ?? 'utf8mb4_unicode_ci';

        $dbName = $this->escapeIdentifier($tenantDb->database_name);

        $tenantDb->status = 'provisioning';
        $tenantDb->last_error = null;
        $tenantDb->save();

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
            if ($seed) {
                DB::connection('tenant')->table('tenant_meta')->updateOrInsert(
                    ['key' => 'provisioned_at'],
                    ['value' => now()->toIso8601String(), 'updated_at' => now(), 'created_at' => now()]
                );
            }

            $tenantDb->status = 'ready';
            $tenantDb->last_error = null;
            $tenantDb->save();
        } catch (Throwable $e) {
            $tenantDb->status = 'failed';
            $tenantDb->last_error = $this->truncateReason($e->getMessage());
            $tenantDb->save();

            report($e);

            return ['ok' => false, 'reason' => $tenantDb->last_error];
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
