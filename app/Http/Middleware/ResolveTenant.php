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
        if (str_starts_with($path, 'admin') || str_starts_with($path, 'super') || str_starts_with($path, 'api/admin')) {
            return $next($request);
        }

        $host = $request->getHost();
        $resolved = $this->resolver->resolveByHost($host);

        if (! $resolved) {
            \abort(404);
        }

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
        } catch (Throwable $e) {
            \report($e);
            \abort(503, 'Tenant DB connection failed');
        }

        $this->context->setTenant($tenant);
        $this->context->setDomain($domain);

        return $next($request);
    }
}
