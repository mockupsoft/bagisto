<?php

namespace App\Console\Commands;

use App\Events\Tenant\TenantProvisioningRequested;
use Illuminate\Console\Command;

class ProvisionTenantCommand extends Command
{
    protected $signature = 'tenants:provision {tenantId}';
    protected $description = 'Dispatch provisioning job for a tenant (stub)';

    public function handle(): int
    {
        $tenantId = (int) $this->argument('tenantId');

        event(new TenantProvisioningRequested($tenantId));

        $this->info("Provisioning event dispatched for tenant {$tenantId}.");

        return self::SUCCESS;
    }
}
