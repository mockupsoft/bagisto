<?php

namespace App\Http\Middleware;

use App\Models\Tenant\TenantDatabase;
use App\Services\Tenant\TenantConnectionConfigurator;
use App\Services\Tenant\TenantResolver;
use App\Support\Tenant\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class ResolveTenant
{
    public function __construct(
        protected TenantResolver $resolver,
        protected TenantContext $context,
    ) {
    }

    public function handle(Request $request, Closure $next)
    {
        $path = ltrim($request->path(), '/');
        $host = $request->getHost();
        
        // Skip tenant resolution for debug routes
        if (str_starts_with($path, '__tenant_debug') || str_starts_with($path, '__tenant_ping')) {
            return $next($request);
        }
        
        // Check if this is a super-admin path (super-admin routes should skip tenant resolution)
        // Super-admin is accessed from main domain, not tenant domains
        $isSuperAdminPath = str_starts_with($path, 'super');
        
        // Check if this is a merchant path (merchant routes should skip tenant resolution)
        $isMerchantPath = str_starts_with($path, 'merchant');
        
        // Try to resolve tenant by host first
        $resolved = $this->resolver->resolveByHost($host);
        
        // Debug: Log resolution attempt
        if (config('app.debug')) {
            \Log::debug('Tenant resolution attempt', [
                'host' => $host,
                'path' => $path,
                'resolved' => $resolved ? 'yes' : 'no',
            ]);
        }
        
        // If tenant is resolved, this is a tenant domain - resolve tenant for all paths including admin
        if ($resolved) {
            $tenant = $resolved['tenant'];
            $domain = $resolved['domain'];

            $dbMeta = TenantDatabase::where('tenant_id', $tenant->id)
                ->whereNull('deleted_at')
                ->first();

            if (! $dbMeta) {
                \abort(503, 'Tenant DB not provisioned');
            }

            try {
                \app(TenantConnectionConfigurator::class)->configure($dbMeta);

                DB::purge('tenant');
                DB::reconnect('tenant');
                
                // Verify connection works
                DB::connection('tenant')->getPdo();
            } catch (Throwable $e) {
                \report($e);
                \abort(503, 'Tenant DB connection failed: ' . $e->getMessage());
            }

            $this->context->setTenant($tenant);
            $this->context->setDomain($domain);

            return $next($request);
        }
        
        // If no tenant resolved, check if this is a path that should skip tenant resolution
        // (super-admin, merchant, or main admin from main domain)
        if ($isSuperAdminPath || $isMerchantPath) {
            return $next($request);
        }
        
        // For admin paths on main domain (super-admin), allow without tenant
        // For other paths on main domain, abort 404
        if (str_starts_with($path, 'admin') || str_starts_with($path, 'api/admin')) {
            // This is main domain admin - allow without tenant
            return $next($request);
        }

        // No tenant found and not a special path - 404
        \abort(404);
    }
}
