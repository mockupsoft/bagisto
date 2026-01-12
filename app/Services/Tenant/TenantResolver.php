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
            return null;
        }

        $domain = Domain::where('domain', $normalized)->first();

        if (! $domain) {
            return null;
        }

        $tenant = $domain->tenant;

        if (! $tenant || $tenant->status !== 'active') {
            return null;
        }

        return [
            'tenant' => $tenant,
            'domain' => $domain,
        ];
    }

    protected function normalizeHost(string $host): string
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
