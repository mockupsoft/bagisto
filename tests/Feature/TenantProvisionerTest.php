<?php

namespace Tests\Feature;

use App\Models\Tenant\Domain;
use App\Models\Tenant\Tenant;
use App\Models\Tenant\TenantDatabase;
use App\Services\Tenant\TenantProvisioner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantProvisionerTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_tenant_creates_primary_domain_and_db_metadata(): void
    {
        $service = app(TenantProvisioner::class);

        $tenant = $service->createTenant([
            'name' => 'Acme Inc',
        ]);

        $this->assertNotNull($tenant->id);

        $this->assertDatabaseHas('domains', [
            'tenant_id' => $tenant->id,
            'is_primary' => true,
            'type' => 'subdomain',
        ]);

        $this->assertDatabaseHas('tenant_databases', [
            'tenant_id' => $tenant->id,
            'status' => 'provisioning',
        ]);
    }

    public function test_attach_custom_domain(): void
    {
        $service = app(TenantProvisioner::class);

        $tenant = $service->createTenant([
            'name' => 'Beta LLC',
        ]);

        $domain = $service->attachCustomDomain($tenant, 'custom.example.com');

        $this->assertInstanceOf(Domain::class, $domain);
        $this->assertFalse($domain->is_primary);
        $this->assertEquals('custom', $domain->type);
        $this->assertNull($domain->verified_at);
    }
}
