<?php

namespace App\Listeners\Tenant;

use App\Events\Tenant\TenantProvisioningRequested;
use App\Jobs\Tenant\ProvisionTenantDatabaseJob;

class DispatchTenantProvisioningJob
{
    public function handle(TenantProvisioningRequested $event): void
    {
        ProvisionTenantDatabaseJob::dispatch($event->tenantId);
    }
}
