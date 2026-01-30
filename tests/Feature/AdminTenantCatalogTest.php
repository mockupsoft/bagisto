<?php

namespace Tests\Feature;

use App\Models\Tenant\Tenant;
use App\Models\Tenant\TenantDatabase;
use App\Services\Tenant\TenantDatabaseProvisioner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\TenantTestContext;
use Tests\TestCase;
use Webkul\User\Models\Admin;
use Webkul\User\Models\Role;

class AdminTenantCatalogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (! env('RUN_TENANT_DDL_TESTS', false)) {
            $this->markTestSkipped('RUN_TENANT_DDL_TESTS env not set');
        }

        TenantTestContext::clearTenantContext();

        config()->set('saas.tenant_db.provisioning_enabled', true);
        config()->set('saas.tenant_db.seed_enabled', true); // Enable seeding for catalog data
    }

    public function test_admin_can_view_tenant_categories_list(): void
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin, 'admin');

        [$tenant, $tenantDb] = $this->provisionTenantWithDb('tenant_categories_test_' . uniqid());

        $response = $this->get(route('admin.tenants.categories.index', $tenant->id));

        $response->assertStatus(200);
        $response->assertSee('Categories');
    }

    public function test_admin_can_view_tenant_attributes_list(): void
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin, 'admin');

        [$tenant, $tenantDb] = $this->provisionTenantWithDb('tenant_attributes_test_' . uniqid());

        $response = $this->get(route('admin.tenants.attributes.index', $tenant->id));

        $response->assertStatus(200);
        $response->assertSee('Attributes');
    }

    public function test_admin_can_view_tenant_attribute_families_list(): void
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin, 'admin');

        [$tenant, $tenantDb] = $this->provisionTenantWithDb('tenant_families_test_' . uniqid());

        $response = $this->get(route('admin.tenants.attribute-families.index', $tenant->id));

        $response->assertStatus(200);
        $response->assertSee('Attribute Families');
    }

    protected function provisionTenantWithDb(string $dbName): array
    {
        $tenant = Tenant::create([
            'name' => $dbName,
            'slug' => $dbName,
            'status' => 'active',
        ]);

        $tenantDb = TenantDatabase::create([
            'tenant_id' => $tenant->id,
            'database_name' => $dbName,
            'database_host' => config('saas.tenant_db.host', '127.0.0.1'),
            'database_port' => config('saas.tenant_db.port', 3306),
            'database_username' => config('saas.tenant_db.username', 'root'),
            'database_password' => config('saas.tenant_db.password', ''),
            'database_prefix' => config('saas.tenant_db.prefix', ''),
            'status' => 'provisioning',
        ]);

        $provisioner = app(TenantDatabaseProvisioner::class);

        $result = $provisioner->provision($tenantDb, [
            'force_enable' => true,
            'seed' => true, // Enable seeding for catalog
        ]);

        if (! $result['ok']) {
            $this->markTestSkipped('Provisioning failed: ' . ($result['reason'] ?? 'unknown'));
        }

        $tenantDb->refresh();

        return [$tenant, $tenantDb];
    }

    protected function makeAdmin(): Admin
    {
        $role = Role::create([
            'name' => 'Administrator',
            'description' => 'All permissions',
            'permission_type' => 'all',
            'permissions' => null,
        ]);

        return Admin::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('admin123'),
            'role_id' => $role->id,
            'status' => 1,
        ]);
    }
}
