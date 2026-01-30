<?php

namespace App\Http\Controllers\Admin;

use App\Models\Tenant\Tenant;
use App\Models\Tenant\TenantDatabase;
use App\Services\Tenant\TenantConnectionConfigurator;
use App\Support\Tenant\TenantContext;
use Illuminate\Support\Facades\DB;

class TenantControllerHelper
{
    /**
     * Set tenant context for admin operations.
     */
    public static function setTenantContext(Tenant $tenant, TenantDatabase $tenantDb): void
    {
        $context = app(TenantContext::class);
        $context->setTenant($tenant);

        app(TenantConnectionConfigurator::class)->configure($tenantDb);

        // Purge and reconnect to ensure fresh connection
        DB::purge('tenant');
        DB::reconnect('tenant');
    }

    /**
     * Clear tenant context after admin operations.
     */
    public static function clearTenantContext(): void
    {
        if (app()->bound(TenantContext::class)) {
            app(TenantContext::class)->clear();
        }

        DB::purge('tenant');
    }
}
