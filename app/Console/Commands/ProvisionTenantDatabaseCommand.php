<?php

namespace App\Console\Commands;

use App\Jobs\Tenant\ProvisionTenantDatabaseJob;
use App\Models\Tenant\TenantDatabase;
use App\Services\Tenant\TenantDatabaseProvisioner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class ProvisionTenantDatabaseCommand extends Command
{
    protected $signature = 'tenants:db:provision {tenantId} {--enable} {--sync} {--seed} {--no-seed}';

    protected $description = 'Provision a tenant database (create DB + run tenant migrations)';

    public function handle(): int
    {
        $tenantId = (int) $this->argument('tenantId');
        $enable = (bool) $this->option('enable');
        $sync = (bool) $this->option('sync');
        $seedOverride = $this->option('seed') ? true : ($this->option('no-seed') ? false : null);

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
            $result = $provisioner->provision($tenantDb, [
                'force_enable' => $enable,
                'seed' => $seedOverride,
            ]);

            $tenantDb->refresh();

            if ($result['ok']) {
                $this->info('Provisioned: ' . $tenantDb->status);
                return self::SUCCESS;
            }

            $this->error('Provision failed: ' . ($tenantDb->last_error ?? $result['reason'] ?? 'unknown'));
            return self::FAILURE;
        }

        // async: optionally override config for this process
        if (! $originalEnabled && $enable) {
            Config::set('saas.tenant_db.provisioning_enabled', true);
        }
        if (! is_null($seedOverride)) {
            Config::set('saas.tenant_db.seed_enabled', $seedOverride);
        }

        ProvisionTenantDatabaseJob::dispatch($tenantId);
        $this->info('Provision job dispatched');

        return self::SUCCESS;
    }
}
