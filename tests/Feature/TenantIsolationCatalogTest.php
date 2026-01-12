<?php

namespace Tests\Feature;

use App\Models\Tenant\Tenant;
use App\Models\Tenant\TenantDatabase;
use App\Services\Tenant\TenantDatabaseProvisioner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Tests\Support\TenantTestContext;
use Tests\TestCase;
use Webkul\Product\Repositories\ProductRepository;
use Webkul\Product\Models\Product;

class TenantIsolationCatalogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Always start with tenant context cleared.
        TenantTestContext::clearTenantContext();

        config()->set('saas.tenant_db.provisioning_enabled', true);
        config()->set('saas.tenant_db.seed_enabled', true);

        // Global channel guarantee (channels stay in global DB)
        $channel = \Webkul\Core\Models\Channel::first();
        if (! $channel) {
            // Create minimal channel if none exists
            $localeId = \Webkul\Core\Models\Locale::firstOrCreate(
                ['code' => 'en'],
                ['name' => 'English', 'direction' => 'ltr']
            )->id;

            $currencyId = \Webkul\Core\Models\Currency::firstOrCreate(
                ['code' => 'USD'],
                ['name' => 'US Dollar', 'symbol' => '$']
            )->id;

            $categoryId = \Webkul\Category\Models\Category::firstOrCreate(
                ['id' => 1],
                ['position' => 1, 'status' => 1, '_lft' => 1, '_rgt' => 2]
            )->id;

            $channel = \Webkul\Core\Models\Channel::create([
                'code' => 'default',
                'name' => 'Default',
                'hostname' => 'localhost',
                'theme' => 'default',
                'root_category_id' => $categoryId,
                'default_locale_id' => $localeId,
                'base_currency_id' => $currencyId,
            ]);
        }

        // Set current channel to prevent null errors
        core()->setCurrentChannel($channel);
    }

    public function test_same_sku_is_isolated_via_repository(): void
    {
        [$tenantA, $dbA] = $this->provisionTenantWithDb('tenant_a');
        [$tenantB, $dbB] = $this->provisionTenantWithDb('tenant_b');

        $repo = app(ProductRepository::class);

        TenantTestContext::setTenantContext($tenantA, $dbA);
        // Reset tenant database to ensure clean state
        TenantTestContext::resetTenantDatabase($dbA);
        
        // Re-set global channel (channels stay in global DB)
        $globalChannel = \Webkul\Core\Models\Channel::first();
        if ($globalChannel) {
            core()->setCurrentChannel($globalChannel);
        }
        
        // Assert connection points to correct database (tenant A)
        $this->assertSame($dbA->database_name, DB::connection('tenant')->getDatabaseName());
        
        // Debug: Model connection name + database
        $connA = Product::query()->getConnection();
        $this->assertSame('tenant', $connA->getName(), 'Product connection name mismatch for tenant A');
        $this->assertSame($dbA->database_name, $connA->getDatabaseName(), 'Product DB mismatch for tenant A');
        
        // Debug: Insert'ten önce global DB'de SKU var mı?
        $this->assertSame(
            0,
            DB::connection(config('database.default'))->table('products')->where('sku', 'ABC-1')->count(),
            'SKU already exists in GLOBAL DB before tenant A insert'
        );
        
        // Debug: Insert'ten önce tenant DB'de SKU var mı?
        $this->assertSame(
            0,
            DB::connection('tenant')->table('products')->where('sku', 'ABC-1')->count(),
            'SKU already exists in TENANT DB before tenant A insert'
        );
        
        $familyA = DB::connection('tenant')->table('attribute_families')->where('code', 'default')->value('id');
        $this->assertNotNull($familyA, 'Attribute family seed missing for tenant A');
        $repo->create(['type' => 'simple', 'sku' => 'ABC-1', 'attribute_family_id' => $familyA]);
        
        // Debug: Insert'ten sonra sadece tenant DB'de var mı?
        $this->assertSame(
            1,
            DB::connection('tenant')->table('products')->where('sku', 'ABC-1')->count(),
            'SKU not found in tenant A DB after insert'
        );

        TenantTestContext::setTenantContext($tenantB, $dbB);
        // Reset tenant database to ensure clean state
        TenantTestContext::resetTenantDatabase($dbB);
        
        // Re-set global channel (channels stay in global DB)
        $globalChannel = \Webkul\Core\Models\Channel::first();
        if ($globalChannel) {
            core()->setCurrentChannel($globalChannel);
        }
        
        // Assert connection points to correct database (tenant B)
        $this->assertSame($dbB->database_name, DB::connection('tenant')->getDatabaseName());
        
        // Debug: Model connection name + database
        $connB = Product::query()->getConnection();
        $this->assertSame('tenant', $connB->getName(), 'Product connection name mismatch for tenant B');
        $this->assertSame($dbB->database_name, $connB->getDatabaseName(), 'Product DB mismatch for tenant B');
        
        // Debug: Insert'ten önce global DB'de SKU var mı?
        $this->assertSame(
            0,
            DB::connection(config('database.default'))->table('products')->where('sku', 'ABC-1')->count(),
            'SKU already exists in GLOBAL DB before tenant B insert'
        );
        
        // Debug: Insert'ten önce tenant DB'de SKU var mı?
        $this->assertSame(
            0,
            DB::connection('tenant')->table('products')->where('sku', 'ABC-1')->count(),
            'SKU already exists in TENANT DB before tenant B insert'
        );
        
        $familyB = DB::connection('tenant')->table('attribute_families')->where('code', 'default')->value('id');
        $this->assertNotNull($familyB, 'Attribute family seed missing for tenant B');
        $repo->create(['type' => 'simple', 'sku' => 'ABC-1', 'attribute_family_id' => $familyB]);
        
        // Debug: Insert'ten sonra sadece tenant DB'de var mı?
        $this->assertSame(
            1,
            DB::connection('tenant')->table('products')->where('sku', 'ABC-1')->count(),
            'SKU not found in tenant B DB after insert'
        );

        TenantTestContext::setTenantContext($tenantA, $dbA);
        $this->assertEquals(1, $repo->findWhere(['sku' => 'ABC-1'])->count());

        TenantTestContext::setTenantContext($tenantB, $dbB);
        $this->assertEquals(1, $repo->findWhere(['sku' => 'ABC-1'])->count());
    }

    public function test_same_sku_is_isolated_via_direct_model_query(): void
    {
        [$tenantA, $dbA] = $this->provisionTenantWithDb('tenant_a2');
        [$tenantB, $dbB] = $this->provisionTenantWithDb('tenant_b2');

        TenantTestContext::setTenantContext($tenantA, $dbA);
        // Assert connection is tenant before any queries
        $connA = Product::query()->getConnection();
        $this->assertSame('tenant', $connA->getName(), 'Product connection name mismatch for tenant A');
        // Assert connection points to correct database (tenant A)
        $this->assertSame($dbA->database_name, DB::connection('tenant')->getDatabaseName());
        $this->assertSame($dbA->database_name, $connA->getDatabaseName(), 'Product DB mismatch for tenant A');
        
        // Debug: Insert'ten önce global DB'de SKU var mı?
        $this->assertSame(
            0,
            DB::connection(config('database.default'))->table('products')->where('sku', 'ABC-1')->count(),
            'SKU already exists in GLOBAL DB before tenant A insert'
        );
        
        $familyA = DB::connection('tenant')->table('attribute_families')->where('code', 'default')->value('id');
        $this->assertNotNull($familyA, 'Attribute family seed missing for tenant A');
        Product::query()->create(['type' => 'simple', 'sku' => 'ABC-1', 'attribute_family_id' => $familyA]);
        
        // Debug: Insert'ten sonra sadece tenant DB'de var mı?
        $this->assertSame(
            1,
            DB::connection('tenant')->table('products')->where('sku', 'ABC-1')->count(),
            'SKU not found in tenant A DB after insert'
        );

        TenantTestContext::setTenantContext($tenantB, $dbB);
        // Reset tenant database to ensure clean state
        TenantTestContext::resetTenantDatabase($dbB);
        
        // Re-set global channel (channels stay in global DB)
        $globalChannel = \Webkul\Core\Models\Channel::first();
        if ($globalChannel) {
            core()->setCurrentChannel($globalChannel);
        }
        
        $connB = Product::query()->getConnection();
        $this->assertSame('tenant', $connB->getName(), 'Product connection name mismatch for tenant B');
        // Assert connection points to correct database (tenant B)
        $this->assertSame($dbB->database_name, DB::connection('tenant')->getDatabaseName());
        $this->assertSame($dbB->database_name, $connB->getDatabaseName(), 'Product DB mismatch for tenant B');
        
        // Debug: Insert'ten önce global DB'de SKU var mı?
        $this->assertSame(
            0,
            DB::connection(config('database.default'))->table('products')->where('sku', 'ABC-1')->count(),
            'SKU already exists in GLOBAL DB before tenant B insert'
        );
        
        $familyB = DB::connection('tenant')->table('attribute_families')->where('code', 'default')->value('id');
        $this->assertNotNull($familyB, 'Attribute family seed missing for tenant B');
        Product::query()->create(['type' => 'simple', 'sku' => 'ABC-1', 'attribute_family_id' => $familyB]);
        
        // Debug: Insert'ten sonra sadece tenant DB'de var mı?
        $this->assertSame(
            1,
            DB::connection('tenant')->table('products')->where('sku', 'ABC-1')->count(),
            'SKU not found in tenant B DB after insert'
        );

        TenantTestContext::setTenantContext($tenantA, $dbA);
        $countA = DB::connection('tenant')->table('products')->where('sku', 'ABC-1')->count();

        TenantTestContext::setTenantContext($tenantB, $dbB);
        $countB = DB::connection('tenant')->table('products')->where('sku', 'ABC-1')->count();

        $this->assertEquals(1, $countA);
        $this->assertEquals(1, $countB);
    }

    public function test_without_tenant_context_uses_global_connection(): void
    {
        TenantTestContext::clearTenantContext();

        $product = Product::query()->create(['type' => 'simple', 'sku' => 'GLOBAL-1']);

        $this->assertNotEquals('tenant', $product->getConnectionName());

        $globalCount = Product::query()->where('sku', 'GLOBAL-1')->count();
        $this->assertEquals(1, $globalCount);
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

        // Reset tenant database to ensure clean state
        TenantTestContext::resetTenantDatabase($tenantDb);

        return [$tenant, $tenantDb];
    }
}
