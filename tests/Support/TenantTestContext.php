<?php

namespace Tests\Support;

use App\Models\Tenant\Tenant;
use App\Models\Tenant\TenantDatabase;
use App\Support\Tenant\TenantContext;
use App\Services\Tenant\TenantConnectionConfigurator;
use Illuminate\Support\Facades\DB;

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
}
