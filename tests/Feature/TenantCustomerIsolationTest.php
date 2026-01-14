<?php

namespace Tests\Feature;

use App\Models\Tenant\Tenant;
use App\Models\Tenant\TenantDatabase;
use App\Services\Tenant\TenantCustomerSeeder;
use App\Services\Tenant\TenantDatabaseProvisioner;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\Support\TenantTestContext;
use Tests\TestCase;
use Webkul\Customer\Models\Customer;
use Webkul\Customer\Models\CustomerAddress;

class TenantCustomerIsolationTest extends TestCase
{
    protected array $connectionsToTransact = [];

    public function test_tenants_allow_same_customer_email(): void
    {
        if (! env('RUN_TENANT_DDL_TESTS', false)) {
            $this->markTestSkipped('RUN_TENANT_DDL_TESTS env not set');
        }

        $template = config('saas.tenant_db.connection_template', 'mysql');
        $provisioned = [];

        [$tenantA, $dbA] = $this->provisionTenantWithDb('tenant_customer_a');
        [$tenantB, $dbB] = $this->provisionTenantWithDb('tenant_customer_b');

        $provisioned[] = [$tenantA, $dbA];
        $provisioned[] = [$tenantB, $dbB];

        $customerSeeder = app(TenantCustomerSeeder::class);

        try {
            TenantTestContext::setTenantContext($tenantA, $dbA);
            TenantTestContext::resetTenantDatabase($dbA);
            TenantTestContext::setTenantContext($tenantA, $dbA);
            $customerSeeder->seed();

            $groupIdA = DB::connection('tenant')->table('customer_groups')->where('code', 'general')->value('id');
            $this->assertNotNull($groupIdA);

            $customerA = Customer::query()->create([
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'same@example.com',
                'password' => bcrypt('secret'),
                'customer_group_id' => $groupIdA,
            ]);

            $this->assertSame('tenant', $customerA->getConnectionName());
            $this->assertSame(1, DB::connection('tenant')->table('customers')->where('email', 'same@example.com')->count());

            // Prove CustomerAddress global scope (address_type='customer') works in tenant DB
            $addressA = new CustomerAddress();
            $addressA->forceFill([
                'customer_id' => $customerA->id,
                'first_name' => 'John',
                'last_name' => 'Doe',
                'address1' => 'Street 1',
                'city' => 'Istanbul',
                'country' => 'TR',
                'postcode' => '34000',
                'phone' => '5550000001',
                // 'address_type' intentionally omitted; should default to 'customer' at DB level
            ])->save();

            $this->assertSame('tenant', $addressA->getConnectionName());
            $this->assertSame(1, DB::connection('tenant')->table('customer_addresses')->count());
            $this->assertSame(1, CustomerAddress::query()->count());

            // Insert a non-customer address_type row and ensure global scope filters it out
            DB::connection('tenant')->table('customer_addresses')->insert([
                'customer_id' => $customerA->id,
                'first_name' => 'John',
                'last_name' => 'Doe',
                'address1' => 'Hidden Street',
                'city' => 'Istanbul',
                'country' => 'TR',
                'postcode' => '34000',
                'phone' => '5550000999',
                'address_type' => 'billing',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->assertSame(2, DB::connection('tenant')->table('customer_addresses')->count());
            $this->assertSame(1, CustomerAddress::query()->count());
            $this->assertSame(2, CustomerAddress::query()->withoutGlobalScopes()->count());

            TenantTestContext::setTenantContext($tenantB, $dbB);
            TenantTestContext::resetTenantDatabase($dbB);
            TenantTestContext::setTenantContext($tenantB, $dbB);
            $customerSeeder->seed();

            $groupIdB = DB::connection('tenant')->table('customer_groups')->where('code', 'general')->value('id');
            $this->assertNotNull($groupIdB);

            $customerB = Customer::query()->create([
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'email' => 'same@example.com',
                'password' => bcrypt('secret'),
                'customer_group_id' => $groupIdB,
            ]);

            $this->assertSame('tenant', $customerB->getConnectionName());
            $this->assertSame(1, DB::connection('tenant')->table('customers')->where('email', 'same@example.com')->count());

            $addressB = new CustomerAddress();
            $addressB->forceFill([
                'customer_id' => $customerB->id,
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'address1' => 'Street 2',
                'city' => 'Ankara',
                'country' => 'TR',
                'postcode' => '06000',
                'phone' => '5550000002',
            ])->save();

            $this->assertSame('tenant', $addressB->getConnectionName());
            $this->assertSame(1, DB::connection('tenant')->table('customer_addresses')->count());
            $this->assertSame(1, CustomerAddress::query()->count());

            DB::connection('tenant')->table('customer_addresses')->insert([
                'customer_id' => $customerB->id,
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'address1' => 'Hidden Street 2',
                'city' => 'Ankara',
                'country' => 'TR',
                'postcode' => '06000',
                'phone' => '5550000998',
                'address_type' => 'billing',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->assertSame(2, DB::connection('tenant')->table('customer_addresses')->count());
            $this->assertSame(1, CustomerAddress::query()->count());
            $this->assertSame(2, CustomerAddress::query()->withoutGlobalScopes()->count());

            TenantTestContext::setTenantContext($tenantA, $dbA);
            $this->assertSame(1, DB::connection('tenant')->table('customers')->where('email', 'same@example.com')->count());
            // tenantA has 2 rows total, but only 1 visible via Eloquent scope
            $this->assertSame(2, DB::connection('tenant')->table('customer_addresses')->count());
            $this->assertSame(1, CustomerAddress::query()->count());
            $this->assertSame(2, CustomerAddress::query()->withoutGlobalScopes()->count());
        } finally {
            TenantTestContext::clearTenantContext();
            $this->cleanupProvisioned($provisioned, $template);
        }
    }

    public function test_customer_auth_guard_is_tenant_scoped(): void
    {
        if (! env('RUN_TENANT_DDL_TESTS', false)) {
            $this->markTestSkipped('RUN_TENANT_DDL_TESTS env not set');
        }

        $template = config('saas.tenant_db.connection_template', 'mysql');
        $provisioned = [];

        [$tenantA, $dbA] = $this->provisionTenantWithDb('tenant_auth_a');
        [$tenantB, $dbB] = $this->provisionTenantWithDb('tenant_auth_b');

        $provisioned[] = [$tenantA, $dbA];
        $provisioned[] = [$tenantB, $dbB];

        $customerSeeder = app(TenantCustomerSeeder::class);

        try {
            // Tenant A setup and login
            TenantTestContext::setTenantContext($tenantA, $dbA);
            TenantTestContext::resetTenantDatabase($dbA);
            TenantTestContext::setTenantContext($tenantA, $dbA);
            $customerSeeder->seed();

            $groupIdA = DB::connection('tenant')->table('customer_groups')->where('code', 'general')->value('id');
            $custA = Customer::query()->create([
                'first_name' => 'Alice',
                'last_name' => 'A',
                'email' => 'login@example.com',
                'password' => bcrypt('secretA'),
                'customer_group_id' => $groupIdA,
            ]);

            $guardA = Auth::guard('customer');
            $guardA->login($custA, true);
            $this->assertTrue($guardA->check());
            $this->assertSame($custA->id, $guardA->id());

            $tokenA = DB::connection('tenant')->table('customers')->where('id', $custA->id)->value('remember_token');
            $this->assertNotEmpty($tokenA);

            $guardA->logout();
            Auth::forgetGuards();
            TenantTestContext::clearTenantContext();

            // Tenant B setup and login
            TenantTestContext::setTenantContext($tenantB, $dbB);
            TenantTestContext::resetTenantDatabase($dbB);
            TenantTestContext::setTenantContext($tenantB, $dbB);
            $customerSeeder->seed();

            $groupIdB = DB::connection('tenant')->table('customer_groups')->where('code', 'general')->value('id');
            $custB = Customer::query()->create([
                'first_name' => 'Bob',
                'last_name' => 'B',
                'email' => 'login@example.com',
                'password' => bcrypt('secretB'),
                'customer_group_id' => $groupIdB,
            ]);

            $guardB = Auth::guard('customer');
            $guardB->login($custB, true);
            $this->assertTrue($guardB->check());
            $this->assertSame($custB->id, $guardB->id());

            $tokenB = DB::connection('tenant')->table('customers')->where('id', $custB->id)->value('remember_token');
            $this->assertNotEmpty($tokenB);
            $this->assertNotEquals($tokenA, $tokenB);

            $guardB->logout();
            Auth::forgetGuards();

            // Switch back to tenant A and ensure session is not authenticated and data remains
            TenantTestContext::setTenantContext($tenantA, $dbA);
            Auth::forgetGuards();
            $this->assertFalse(Auth::guard('customer')->check());
            $this->assertSame(1, DB::connection('tenant')->table('customers')->where('email', 'login@example.com')->count());
        } finally {
            TenantTestContext::clearTenantContext();
            $this->cleanupProvisioned($provisioned, $template);
        }
    }

    public function test_without_tenant_context_stays_global(): void
    {
        TenantTestContext::clearTenantContext();

        $customer = new Customer();

        $this->assertNotSame('tenant', $customer->getConnectionName());
    }

    protected function provisionTenantWithDb(string $dbName): array
    {
        $dbName = $dbName . '_' . Str::lower(Str::random(6));

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
            DB::connection(config('saas.tenant_db.connection_template', 'mysql'))->statement(
                'DROP DATABASE IF EXISTS `' . str_replace('`', '``', $dbName) . '`'
            );
            $tenantDb->delete();
            $tenant->delete();

            $this->markTestSkipped('Provisioning failed: ' . ($result['reason'] ?? 'unknown'));
        }

        $tenantDb->refresh();

        return [$tenant, $tenantDb];
    }

    private function cleanupProvisioned(array $provisioned, string $template): void
    {
        foreach ($provisioned as [$tenant, $db]) {
            try {
                $db->refresh();

                DB::connection($template)->statement(
                    'DROP DATABASE IF EXISTS `' . str_replace('`', '``', $db->database_name) . '`'
                );

                $db->delete();
                $tenant->delete();
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }
}
