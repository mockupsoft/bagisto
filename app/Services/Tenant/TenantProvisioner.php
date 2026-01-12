<?php

namespace App\Services\Tenant;

use App\Events\Tenant\TenantProvisioningRequested;
use App\Models\Tenant\Domain;
use App\Models\Tenant\Tenant;
use App\Models\Tenant\TenantDatabase;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TenantProvisioner
{
    public function createTenant(array $data): Tenant
    {
        return DB::transaction(function () use ($data) {
            $name = Arr::get($data, 'name');
            $providedSlug = Arr::get($data, 'slug');
            $slug = $providedSlug ? Str::slug(Str::lower($providedSlug)) : Str::slug(Str::lower($name));

            $tenant = Tenant::create([
                'name' => $name,
                'slug' => $slug,
                'status' => Arr::get($data, 'status', 'active'),
                'plan' => Arr::get($data, 'plan'),
            ]);

            $baseDomain = Config::get('saas.base_domain', 'example.test');
            $primaryDomain = $slug . '.' . $baseDomain;

            Domain::create([
                'tenant_id' => $tenant->id,
                'domain' => $primaryDomain,
                'type' => 'subdomain',
                'is_primary' => true,
                'verified_at' => null,
                'created_by_id' => Arr::get($data, 'created_by_id'),
                'note' => null,
            ]);

            $dbConfig = Config::get('saas.tenant_db', []);
            $dbName = ($dbConfig['name_prefix'] ?? 'tenant_') . $tenant->id;

            TenantDatabase::create([
                'tenant_id' => $tenant->id,
                'database_name' => $dbName,
                'database_host' => $dbConfig['host'] ?? '127.0.0.1',
                'database_port' => $dbConfig['port'] ?? 3306,
                'database_username' => $dbConfig['username'] ?? 'root',
                'database_password' => $dbConfig['password'] ?? '',
                'database_prefix' => $dbConfig['prefix'] ?? '',
                'status' => 'provisioning',
                'last_error' => null,
            ]);

            event(new TenantProvisioningRequested($tenant->id));

            return $tenant;
        });
    }

    public function attachCustomDomain(Tenant $tenant, string $domain, ?int $createdById = null, ?string $note = null): Domain
    {
        $cleanDomain = trim(Str::lower($domain));

        if ($cleanDomain === '' || str_contains($cleanDomain, ' ') || ! str_contains($cleanDomain, '.')) {
            throw new \InvalidArgumentException('Invalid domain format.');
        }

        return Domain::create([
            'tenant_id' => $tenant->id,
            'domain' => $cleanDomain,
            'type' => 'custom',
            'is_primary' => false,
            'verified_at' => null,
            'created_by_id' => $createdById,
            'note' => $note,
        ]);
    }
}
