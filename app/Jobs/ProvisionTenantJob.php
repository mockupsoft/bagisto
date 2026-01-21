<?php

namespace App\Jobs;

use App\Models\Tenant\Tenant;
use App\Models\Tenant\TenantDatabase;
use App\Services\Tenant\TenantCatalogSeeder;
use App\Services\Tenant\TenantConnectionConfigurator;
use App\Services\Tenant\TenantCustomerSeeder;
use App\Services\Tenant\TenantSalesSeeder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Throwable;

class ProvisionTenantJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tenantId;

    public string $adminEmail;

    public string $adminPasswordHash;

    public string $adminName;

    public function __construct(int $tenantId, string $adminEmail, string $adminPasswordHash, string $adminName = 'Admin')
    {
        $this->tenantId = $tenantId;
        $this->adminEmail = $adminEmail;
        $this->adminPasswordHash = $adminPasswordHash;
        $this->adminName = $adminName;
    }

    public function handle(
        TenantConnectionConfigurator $configurator,
        TenantCatalogSeeder $catalogSeeder,
        TenantCustomerSeeder $customerSeeder,
        TenantSalesSeeder $salesSeeder
    ): void {
        $tenant = Tenant::find($this->tenantId);

        if (! $tenant) {
            return;
        }

        $tenant->status = 'provisioning';
        $tenant->provisioning_started_at = $tenant->provisioning_started_at ?? now();
        $tenant->last_error = null;
        $tenant->save();

        $config = Config::get('saas.tenant_db', []);
        $dbName = ($config['name_prefix'] ?? 'tenant_') . $tenant->id;

        $tenantDb = TenantDatabase::firstOrCreate(
            ['tenant_id' => $tenant->id],
            [
                'database_name' => $dbName,
                'database_host' => $config['host'] ?? '127.0.0.1',
                'database_port' => $config['port'] ?? 3306,
                'database_username' => $config['username'] ?? 'root',
                'database_password' => $config['password'] ?? '',
                'database_prefix' => $config['prefix'] ?? '',
                'status' => 'pending',
                'last_error' => null,
            ]
        );

        $tenantDb->status = 'provisioning';
        $tenantDb->last_error = null;
        $tenantDb->save();

        try {
            $templateConnection = $config['connection_template'] ?? 'mysql';
            $charset = $config['charset'] ?? 'utf8mb4';
            $collation = $config['collation'] ?? 'utf8mb4_unicode_ci';
            $escapedDbName = str_replace('`', '``', $tenantDb->database_name ?: $dbName);

            DB::connection($templateConnection)->statement(
                sprintf(
                    'CREATE DATABASE IF NOT EXISTS `%s` CHARACTER SET %s COLLATE %s',
                    $escapedDbName,
                    $charset,
                    $collation
                )
            );

            $configurator->configure($tenantDb);
            DB::purge('tenant');
            DB::reconnect('tenant');

            Artisan::call('migrate', [
                '--database' => 'tenant',
                '--path' => $config['migrations_path'] ?? 'database/migrations/tenant',
                '--force' => true,
            ]);

            $seedEnabled = (bool) ($config['seed_enabled'] ?? false);

            if ($seedEnabled) {
                $catalogSeeder->seed();
                $customerSeeder->seed();
                $salesSeeder->seed();
            }

            $this->bootstrapTenantAdmin();

            $tenant->status = 'active';
            $tenant->provisioning_finished_at = now();
            $tenant->last_error = null;
            $tenant->save();

            $tenantDb->status = 'ready';
            $tenantDb->last_error = null;
            $tenantDb->save();
        } catch (Throwable $e) {
            $reason = mb_strimwidth($e->getMessage(), 0, 1000);

            $tenant->status = 'failed';
            $tenant->provisioning_finished_at = now();
            $tenant->last_error = $reason;
            $tenant->save();

            $tenantDb->status = 'failed';
            $tenantDb->last_error = $reason;
            $tenantDb->save();

            throw $e;
        }
    }

    protected function bootstrapTenantAdmin(): void
    {
        $conn = DB::connection('tenant');
        $schema = $conn->getSchemaBuilder();

        if (! $schema->hasTable('roles') || ! $schema->hasTable('admins')) {
            return;
        }

        $roleId = $conn->table('roles')->where('permission_type', 'all')->value('id');

        if (! $roleId) {
            $roleId = $conn->table('roles')->insertGetId([
                'name' => 'Administrator',
                'description' => 'Tenant administrator role',
                'permission_type' => 'all',
                'permissions' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $now = now();

        $existingAdminId = $conn->table('admins')->where('email', $this->adminEmail)->value('id');

        if ($existingAdminId) {
            $conn->table('admins')->where('id', $existingAdminId)->update([
                'name' => $this->adminName,
                'password' => $this->adminPasswordHash,
                'status' => 1,
                'role_id' => $roleId,
                'updated_at' => $now,
            ]);

            return;
        }

        $conn->table('admins')->insert([
            'name' => $this->adminName,
            'email' => $this->adminEmail,
            'password' => $this->adminPasswordHash,
            'status' => 1,
            'role_id' => $roleId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
