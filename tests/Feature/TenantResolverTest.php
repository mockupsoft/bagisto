<?php

namespace Tests\Feature;

use App\Models\Tenant\Domain;
use App\Models\Tenant\Tenant;
use App\Models\Tenant\TenantDatabase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TenantResolverTest extends TestCase
{
    use RefreshDatabase;

    public function test_resolves_active_tenant_and_configures_connection(): void
    {
        $tenant = Tenant::create([
            'name' => 'Acme Inc',
            'slug' => 'acme',
            'status' => 'active',
        ]);

        Domain::create([
            'tenant_id' => $tenant->id,
            'domain' => 'acme.example.test',
            'type' => 'subdomain',
            'is_primary' => true,
        ]);

        TenantDatabase::create([
            'tenant_id' => $tenant->id,
            'database_name' => 'tenant_acme',
            'database_host' => '127.0.0.1',
            'database_port' => 3306,
            'database_username' => 'root',
            'database_password' => 'secret',
            'status' => 'provisioning',
        ]);

        $this->get('/__tenant_ping', ['Host' => 'acme.example.test'])
            ->assertOk();

        $this->assertEquals('tenant_acme', config('database.connections.tenant.database'));
    }

    public function test_suspended_tenant_returns_404(): void
    {
        $tenant = Tenant::create([
            'name' => 'Beta LLC',
            'slug' => 'beta',
            'status' => 'suspended',
        ]);

        Domain::create([
            'tenant_id' => $tenant->id,
            'domain' => 'beta.example.test',
            'type' => 'subdomain',
            'is_primary' => true,
        ]);

        $this->get('/__tenant_ping', ['Host' => 'beta.example.test'])
            ->assertNotFound();
    }
}
