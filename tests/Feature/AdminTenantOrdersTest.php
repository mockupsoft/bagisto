<?php

namespace Tests\Feature;

use App\Models\Tenant\Tenant;
use App\Models\Tenant\TenantDatabase;
use App\Services\Tenant\TenantDatabaseProvisioner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\TenantTestContext;
use Tests\TestCase;
use Webkul\Sales\Models\Order;
use Webkul\User\Models\Admin;
use Webkul\User\Models\Role;

class AdminTenantOrdersTest extends TestCase
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
        config()->set('saas.tenant_db.seed_enabled', true);
    }

    public function test_admin_can_view_tenant_order_detail(): void
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin, 'admin');

        [$tenant, $tenantDb] = $this->provisionTenantWithDb('tenant_order_detail_' . uniqid());

        TenantTestContext::setTenantContext($tenant, $tenantDb);

        $order = Order::query()->create([
            'increment_id' => '000000001',
            'status' => 'pending',
            'base_grand_total' => 100.00,
            'customer_email' => 'test@example.com',
            'customer_first_name' => 'Test',
            'customer_last_name' => 'Customer',
        ]);

        TenantTestContext::clearTenantContext();

        $response = $this->get(route('admin.tenants.orders.show', [$tenant->id, $order->id]));

        $response->assertStatus(200);
        $response->assertSee('000000001');
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
            'seed' => true,
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
