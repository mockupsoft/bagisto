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

        // Guard: Ensure route is registered
        $this->assertTrue(
            collect(app('router')->getRoutes())->contains(fn ($r) => $r->uri() === '__tenant_ping'),
            'Route __tenant_ping must be registered in test environment'
        );

        $this->get('http://acme.example.test/__tenant_ping')
            ->assertOk();

        $this->assertEquals('tenant_acme', config('database.connections.tenant.database'));
    }

    public function test_verified_custom_domain_resolves(): void
    {
        $tenant = Tenant::create([
            'name' => 'Gamma Inc',
            'slug' => 'gamma',
            'status' => 'active',
        ]);

        Domain::create([
            'tenant_id' => $tenant->id,
            'domain' => 'custom.example.test',
            'type' => 'custom',
            'is_primary' => false,
            'verified_at' => now(),
        ]);

        TenantDatabase::create([
            'tenant_id' => $tenant->id,
            'database_name' => 'tenant_gamma',
            'database_host' => '127.0.0.1',
            'database_port' => 3306,
            'database_username' => 'root',
            'database_password' => 'secret',
            'status' => 'provisioning',
        ]);

        $this->get('http://custom.example.test/__tenant_ping')
            ->assertOk();
    }

    public function test_unverified_custom_domain_returns_404(): void
    {
        $tenant = Tenant::create([
            'name' => 'Delta Inc',
            'slug' => 'delta',
            'status' => 'active',
        ]);

        Domain::create([
            'tenant_id' => $tenant->id,
            'domain' => 'unverified.example.test',
            'type' => 'custom',
            'is_primary' => false,
            'verified_at' => null,
        ]);

        TenantDatabase::create([
            'tenant_id' => $tenant->id,
            'database_name' => 'tenant_delta',
            'database_host' => '127.0.0.1',
            'database_port' => 3306,
            'database_username' => 'root',
            'database_password' => 'secret',
            'status' => 'provisioning',
        ]);

        $this->get('http://unverified.example.test/__tenant_ping')
            ->assertNotFound();
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

        $this->get('http://beta.example.test/__tenant_ping')
            ->assertNotFound();
    }
}
