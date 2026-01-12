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

        app(TenantConnectionConfigurator::class)->configure($tenantDb);

        DB::purge('tenant');
        DB::reconnect('tenant');
    }

    public static function clearTenantContext(): void
    {
        if (app()->bound(TenantContext::class)) {
            app(TenantContext::class)->clear();
        }

        DB::purge('tenant');
    }
}
