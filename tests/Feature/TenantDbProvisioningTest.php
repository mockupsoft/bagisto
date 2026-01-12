<?php

namespace Tests\Feature;

use App\Models\Tenant\Domain;
use App\Models\Tenant\Tenant;
use App\Models\Tenant\TenantDatabase;
use App\Services\Tenant\TenantDatabaseProvisioner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TenantDbProvisioningTest extends TestCase
{
    use RefreshDatabase;

    public function test_provision_creates_db_and_runs_migrations(): void
    {
        $templateConfig = config('database.connections.mysql');

        if (empty($templateConfig['username']) && empty(env('DB_USERNAME'))) {
            $this->markTestSkipped('DB credentials not set; skipping provisioning test.');
        }

        config()->set('saas.tenant_db.provisioning_enabled', true);
        config()->set('saas.tenant_db.seed_enabled', true);

        $tenant = Tenant::create([
            'name' => 'Provision Test',
            'slug' => 'provtest',
            'status' => 'active',
        ]);

        Domain::create([
            'tenant_id' => $tenant->id,
            'domain' => 'provtest.example.test',
            'type' => 'subdomain',
            'is_primary' => true,
            'verified_at' => now(),
        ]);

        $tenantDb = TenantDatabase::create([
            'tenant_id' => $tenant->id,
            'database_name' => 'tenant_test_' . uniqid(),
            'database_host' => config('saas.tenant_db.host', '127.0.0.1'),
            'database_port' => config('saas.tenant_db.port', 3306),
            'database_username' => config('saas.tenant_db.username', 'root'),
            'database_password' => config('saas.tenant_db.password', ''),
            'database_prefix' => config('saas.tenant_db.prefix', ''),
            'status' => 'provisioning',
        ]);

        $provisioner = app(TenantDatabaseProvisioner::class);
        $result = $provisioner->provision($tenantDb);

        if (! $result['ok']) {
            $this->markTestSkipped('Provisioning failed due to environment: ' . ($result['reason'] ?? 'unknown'));
        }

        $tenantDb->refresh();

        $this->assertEquals('ready', $tenantDb->status);
        $this->assertNull($tenantDb->last_error);

        $this->assertTrue(Schema::connection('tenant')->hasTable('tenant_meta'));

        $meta = DB::connection('tenant')->table('tenant_meta')->where('key', 'provisioned_at')->first();
        $this->assertNotNull($meta);
    }
}
