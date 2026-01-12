<?php

namespace Tests\Feature;

use App\Models\Tenant\Domain;
use App\Models\Tenant\Tenant;
use App\Models\Tenant\TenantDatabase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantChannelLocaleSafetyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedTenant();
    }

    public function test_tenant_ping_does_not_500_when_channel_missing(): void
    {
        $response = $this->get('/__tenant_ping', ['Host' => 'acme.example.test']);

        $this->assertNotEquals(500, $response->getStatusCode());
    }

    public function test_admin_routes_bypass_tenant_channel(): void
    {
        $response = $this->get('/admin/login');
        $this->assertTrue(in_array($response->getStatusCode(), [200, 302, 303]));

        $responseHost = $this->get('/admin/login', ['Host' => 'acme.example.test']);
        $this->assertTrue(in_array($responseHost->getStatusCode(), [200, 302, 303]));
    }

    protected function seedTenant(): void
    {
        $tenant = Tenant::create([
            'name' => 'Acme',
            'slug' => 'acme',
            'status' => 'active',
        ]);

        Domain::create([
            'tenant_id' => $tenant->id,
            'domain' => 'acme.example.test',
            'type' => 'subdomain',
            'is_primary' => true,
            'verified_at' => now(),
        ]);

        $default = config('database.connections.mysql');

        TenantDatabase::create([
            'tenant_id' => $tenant->id,
            'database_name' => $default['database'],
            'database_host' => $default['host'] ?? '127.0.0.1',
            'database_port' => $default['port'] ?? 3306,
            'database_username' => $default['username'] ?? '',
            'database_password' => $default['password'] ?? '',
            'database_prefix' => $default['prefix'] ?? '',
            'status' => 'ready',
        ]);
    }
}
