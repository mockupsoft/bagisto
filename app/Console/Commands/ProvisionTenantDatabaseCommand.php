<?php

namespace App\Console\Commands;

use App\Jobs\Tenant\ProvisionTenantDatabaseJob;
use App\Models\Tenant\TenantDatabase;
use App\Services\Tenant\TenantDatabaseProvisioner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class ProvisionTenantDatabaseCommand extends Command
{
    protected $signature = 'tenants:db:provision {tenantId} {--enable} {--sync}';

    protected $description = 'Provision a tenant database (create DB + run tenant migrations)';

    public function handle(): int
    {
        $tenantId = (int) $this->argument('tenantId');
        $enable = (bool) $this->option('enable');
        $sync = (bool) $this->option('sync');

        $tenantDb = TenantDatabase::where('tenant_id', $tenantId)
            ->whereNull('deleted_at')
            ->first();

        if (! $tenantDb) {
            $this->error('Tenant DB metadata not found');
            return self::FAILURE;
        }

        $originalEnabled = Config::get('saas.tenant_db.provisioning_enabled', false);
        if (! $originalEnabled && $enable) {
            Config::set('saas.tenant_db.provisioning_enabled', true);
        }

        if ($sync) {
            $provisioner = app(TenantDatabaseProvisioner::class);
            $result = $provisioner->provision($tenantDb);

            if ($result['ok']) {
                $this->info('Provisioned: ready');
                return self::SUCCESS;
            }

            $this->error('Provision failed: ' . ($result['reason'] ?? 'unknown'));
            return self::FAILURE;
        }

        ProvisionTenantDatabaseJob::dispatch($tenantId);
        $this->info('Provision job dispatched');

        return self::SUCCESS;
    }
}
