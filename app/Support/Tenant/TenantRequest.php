<?php

namespace App\Support\Tenant;

use Illuminate\Http\Request;

class TenantRequest
{
    public static function isAdminPath(Request $request): bool
    {
        $path = ltrim($request->path(), '/');

        return str_starts_with($path, 'admin')
            || str_starts_with($path, 'super')
            || str_starts_with($path, 'api/admin');
    }

    public static function isTenantResolved(): bool
    {
        return app()->bound(TenantContext::class) && app(TenantContext::class)->tenant() !== null;
    }

    public static function getHost(Request $request): string
    {
        $host = strtolower(trim($request->getHost())) ?: '';

        if (str_contains($host, ':')) {
            $host = explode(':', $host, 2)[0];
        }

        return rtrim($host, '.');
    }
}
