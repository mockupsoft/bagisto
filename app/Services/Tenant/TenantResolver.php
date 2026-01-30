<?php

namespace App\Services\Tenant;

use App\Models\Tenant\Domain;
use App\Models\Tenant\Tenant;
use Illuminate\Support\Str;

class TenantResolver
{
    /**
     * Resolve tenant and domain by host.
     *
     * @return array{tenant: Tenant, domain: Domain}|null
     */
    public function resolveByHost(string $host): ?array
    {
        $normalized = $this->normalizeHost($host);

        if ($normalized === '') {
            if (config('app.debug')) {
                \Log::debug('TenantResolver: Empty normalized host', ['host' => $host]);
            }
            return null;
        }

        $domain = Domain::where('domain', $normalized)
            ->first();

        if (! $domain) {
            if (config('app.debug')) {
                \Log::debug('TenantResolver: Domain not found', [
                    'host' => $host,
                    'normalized' => $normalized,
                    'available_domains' => Domain::pluck('domain')->toArray(),
                ]);
            }
            return null;
        }

        if ($domain->type === 'custom' && is_null($domain->verified_at)) {
            return null;
        }

        $tenant = $domain->tenant;

        if (! $tenant) {
            return null;
        }

        // Allow active, ready, and provisioning tenants
        // Provisioning tenants can access admin panel to see progress
        if (! in_array($tenant->status, ['active', 'ready', 'provisioning'])) {
            return null;
        }

        return [
            'tenant' => $tenant,
            'domain' => $domain,
        ];
    }

    public function normalizeHost(string $host): string
    {
        $host = trim(Str::lower($host));

        if ($host === '') {
            return '';
        }

        if (str_contains($host, ':')) {
            $host = explode(':', $host, 2)[0];
        }

        return rtrim($host, '.');
    }
}
