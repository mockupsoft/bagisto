<?php

namespace Tests\Feature;

use App\Models\Tenant\Tenant;
use App\Models\Tenant\TenantDatabase;
use App\Services\Tenant\TenantDatabaseProvisioner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\TenantTestContext;
use Tests\TestCase;
use Webkul\Customer\Models\Customer;
use Webkul\User\Models\Admin;
use Webkul\User\Models\Role;

class AdminTenantCustomersTest extends TestCase
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
        config()->set('saas.tenant_db.seed_enabled', false);
    }

    public function test_admin_can_view_tenant_customers_list(): void
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin, 'admin');

        [$tenant, $tenantDb] = $this->provisionTenantWithDb('tenant_customers_test_' . uniqid());

        TenantTestContext::setTenantContext($tenant, $tenantDb);

        // Create a test customer in tenant DB
        $customer = Customer::query()->create([
            'first_name' => 'Test',
            'last_name' => 'Customer',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'status' => true,
        ]);

        TenantTestContext::clearTenantContext();

        $response = $this->get(route('admin.tenants.customers.index', $tenant->id));

        $response->assertStatus(200);
        $response->assertSee('Customers');
    }

    public function test_admin_can_view_tenant_customer_detail(): void
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin, 'admin');

        [$tenant, $tenantDb] = $this->provisionTenantWithDb('tenant_customer_detail_' . uniqid());

        TenantTestContext::setTenantContext($tenant, $tenantDb);

        $customer = Customer::query()->create([
            'first_name' => 'Test',
            'last_name' => 'Customer',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'status' => true,
        ]);

        TenantTestContext::clearTenantContext();

        $response = $this->get(route('admin.tenants.customers.show', [$tenant->id, $customer->id]));

        $response->assertStatus(200);
        $response->assertSee('Test Customer');
        $response->assertSee('test@example.com');
    }

    public function test_admin_can_suspend_tenant_customer(): void
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin, 'admin');

        [$tenant, $tenantDb] = $this->provisionTenantWithDb('tenant_customer_suspend_' . uniqid());

        TenantTestContext::setTenantContext($tenant, $tenantDb);

        $customer = Customer::query()->create([
            'first_name' => 'Test',
            'last_name' => 'Customer',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'status' => true,
            'is_suspended' => false,
        ]);

        TenantTestContext::clearTenantContext();

        $response = $this->post(route('admin.tenants.customers.suspend', [$tenant->id, $customer->id]));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        TenantTestContext::setTenantContext($tenant, $tenantDb);
        $customer->refresh();
        $this->assertTrue($customer->is_suspended);
        TenantTestContext::clearTenantContext();
    }

    public function test_admin_can_activate_tenant_customer(): void
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin, 'admin');

        [$tenant, $tenantDb] = $this->provisionTenantWithDb('tenant_customer_activate_' . uniqid());

        TenantTestContext::setTenantContext($tenant, $tenantDb);

        $customer = Customer::query()->create([
            'first_name' => 'Test',
            'last_name' => 'Customer',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'status' => false,
            'is_suspended' => true,
        ]);

        TenantTestContext::clearTenantContext();

        $response = $this->post(route('admin.tenants.customers.activate', [$tenant->id, $customer->id]));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        TenantTestContext::setTenantContext($tenant, $tenantDb);
        $customer->refresh();
        $this->assertFalse($customer->is_suspended);
        $this->assertTrue($customer->status);
        TenantTestContext::clearTenantContext();
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
            'seed' => false,
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
