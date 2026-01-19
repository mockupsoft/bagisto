<?php

namespace Tests\Feature;

use App\Jobs\ProvisionTenantJob;
use App\Models\MerchantUser;
use App\Services\Tenant\TenantProvisioner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use Webkul\Core\Facades\Core;
use Webkul\User\Models\Admin;
use Webkul\User\Models\Role;


class AdminTenantManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fakeCoreContext();
    }

    public function test_admin_can_list_and_view_tenants(): void
    {
        $admin = $this->makeAdmin();

        $tenant = app(TenantProvisioner::class)->createTenant(['name' => 'Acme', 'status' => 'active']);

        $this->actingAs($admin, 'admin')->get(route('admin.tenants.index'))
            ->assertOk()
            ->assertSee((string) $tenant->id);

        $this->actingAs($admin, 'admin')->get(route('admin.tenants.show', ['tenant' => $tenant->id]))
            ->assertOk()
            ->assertSee($tenant->slug);
    }

    public function test_admin_can_retry_provisioning_dispatches_job(): void
    {
        Queue::fake();

        $admin = $this->makeAdmin();

        $tenant = app(TenantProvisioner::class)->createTenant(['name' => 'Acme', 'status' => 'active']);

        MerchantUser::create([
            'tenant_id' => $tenant->id,
            'name' => 'Merchant',
            'email' => 'merchant@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.tenants.retry', ['tenant' => $tenant->id]))
            ->assertRedirect();

        Queue::assertPushed(ProvisionTenantJob::class);
    }

    public function test_admin_can_toggle_tenant_status(): void
    {
        $admin = $this->makeAdmin();

        $tenant = app(TenantProvisioner::class)->createTenant(['name' => 'Acme', 'status' => 'active']);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.tenants.toggle', ['tenant' => $tenant->id]))
            ->assertRedirect();

        $this->assertSame('inactive', $tenant->refresh()->status);
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

    protected function fakeCoreContext(): void
    {
        $fake = new class
        {
            public function getCurrentLocale(): object
            {
                return (object) ['code' => 'en', 'direction' => 'ltr'];
            }

            public function getBaseCurrency(): object
            {
                return new class
                {
                    public function toJson(): string
                    {
                        return json_encode(['code' => 'USD']);
                    }
                };
            }

            public function getCurrentChannelCode(): string
            {
                return 'default';
            }

            public function getDefaultChannelCode(): string
            {
                return 'default';
            }

            public function getRequestedChannelCode($fallback = true): string
            {
                return 'default';
            }

            public function getConfigData($key, $channel = null, $locale = null)
            {
                return null;
            }

            public function version(): string
            {
                return 'test';
            }

            public function __call($name, $arguments)
            {
                return null;
            }
        };

        Core::swap($fake);
    }
}
