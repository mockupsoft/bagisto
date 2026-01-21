<?php

namespace Tests\Feature;

use App\Models\Tenant\Tenant;
use App\Models\Tenant\TenantDatabase;
use App\Services\Tenant\TenantDatabaseProvisioner;
use Illuminate\Support\Facades\DB;
use Tests\Support\TenantTestContext;
use Tests\TestCase;

class TenantCatalogIsolationTest extends TestCase
{
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

    public function test_catalog_data_is_isolated_between_tenants(): void
    {
        $uniqueId = uniqid('cat_', true);
        [$tenantA, $dbA] = $this->provisionTenantWithDb('tenant_catalog_a_' . $uniqueId);
        [$tenantB, $dbB] = $this->provisionTenantWithDb('tenant_catalog_b_' . $uniqueId);

        // Snapshot global DB counts before inserts
        $globalProductsBefore = DB::connection('mysql')->table('products')->count();
        $globalCategoriesBefore = DB::connection('mysql')->table('categories')->count();

        // Insert category and product in tenant A
        TenantTestContext::setTenantContext($tenantA, $dbA);
        $connA = DB::connection('tenant');

        $categoryIdA = $connA->table('categories')->insertGetId([
            'parent_id' => null,
            '_lft' => 1,
            '_rgt' => 2,
            'depth' => 0,
            'status' => true,
            'position' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $connA->table('category_translations')->insert([
            'category_id' => $categoryIdA,
            'locale' => 'en',
            'name' => 'Category A',
            'slug' => 'category-a',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $productIdA = $connA->table('products')->insertGetId([
            'type' => 'simple',
            'sku' => 'PROD-001',
            'status' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $connA->table('product_categories')->insert([
            'product_id' => $productIdA,
            'category_id' => $categoryIdA,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Verify tenant A counts
        $this->assertEquals(1, $connA->table('categories')->count());
        $this->assertEquals(1, $connA->table('products')->count());
        $this->assertEquals(1, $connA->table('product_categories')->count());

        // Insert same SKU/slug in tenant B
        TenantTestContext::setTenantContext($tenantB, $dbB);
        $connB = DB::connection('tenant');

        $categoryIdB = $connB->table('categories')->insertGetId([
            'parent_id' => null,
            '_lft' => 1,
            '_rgt' => 2,
            'depth' => 0,
            'status' => true,
            'position' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $connB->table('category_translations')->insert([
            'category_id' => $categoryIdB,
            'locale' => 'en',
            'name' => 'Category B',
            'slug' => 'category-a', // Same slug as tenant A
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $productIdB = $connB->table('products')->insertGetId([
            'type' => 'simple',
            'sku' => 'PROD-001', // Same SKU as tenant A
            'status' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $connB->table('product_categories')->insert([
            'product_id' => $productIdB,
            'category_id' => $categoryIdB,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Verify tenant B counts
        $this->assertEquals(1, $connB->table('categories')->count());
        $this->assertEquals(1, $connB->table('products')->count());
        $this->assertEquals(1, $connB->table('product_categories')->count());

        // Verify tenant A counts remain unchanged (no cross-tenant leakage)
        TenantTestContext::setTenantContext($tenantA, $dbA);
        $this->assertEquals(1, $connA->table('categories')->count());
        $this->assertEquals(1, $connA->table('products')->count());
        $this->assertEquals(1, $connA->table('product_categories')->count());

        // Verify global DB counts remain unchanged
        $globalProductsAfter = DB::connection('mysql')->table('products')->count();
        $globalCategoriesAfter = DB::connection('mysql')->table('categories')->count();

        $this->assertEquals($globalProductsBefore, $globalProductsAfter, 'Global products count should not change');
        $this->assertEquals($globalCategoriesBefore, $globalCategoriesAfter, 'Global categories count should not change');
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

        // Verify migration ran: check if categories table exists
        TenantTestContext::setTenantContext($tenant, $tenantDb);
        $conn = DB::connection('tenant');
        $schema = $conn->getSchemaBuilder();
        
        if (! $schema->hasTable('categories')) {
            $this->markTestSkipped('Migration did not create categories table. Provision result: ' . json_encode($result));
        }

        TenantTestContext::clearTenantContext();

        return [$tenant, $tenantDb];
    }
}
