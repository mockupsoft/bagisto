<?php

namespace Tests\Feature;

use App\Models\Tenant\Domain;
use App\Models\Tenant\Tenant;
use App\Models\Tenant\TenantDatabase;
use App\Services\Tenant\TenantCatalogSeeder;
use App\Services\Tenant\TenantDatabaseProvisioner;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\Support\TenantTestContext;
use Tests\TestCase;

class TenantCatalogIsolationTest extends TestCase
{
    protected array $connectionsToTransact = [];

    public function test_tenant_catalog_data_is_isolated(): void
    {
        if (! env('RUN_TENANT_DDL_TESTS', false)) {
            $this->markTestSkipped('RUN_TENANT_DDL_TESTS env not set');
        }

        $template = config('saas.tenant_db.connection_template', 'mysql');
        $globalCountsBefore = $this->captureGlobalCounts($template);
        $provisioned = [];

        [$tenantA, $dbA, $hostA] = $this->provisionTenantWithDb('tenant_catalog_a');
        [$tenantB, $dbB, $hostB] = $this->provisionTenantWithDb('tenant_catalog_b');

        $provisioned[] = [$tenantA, $dbA];
        $provisioned[] = [$tenantB, $dbB];

        try {
            $this->runCatalogFlow($tenantA, $dbA, 'SAME-SKU', 'Same Product', 'default');

            TenantTestContext::setTenantContext($tenantA, $dbA);
            $this->assertSame(1, DB::connection('tenant')->table('products')->count());
            $this->assertSame(1, DB::connection('tenant')->table('product_categories')->count());
            $this->assertSame(1, DB::connection('tenant')->table('product_attribute_values')->count());

            TenantTestContext::setTenantContext($tenantB, $dbB);
            $this->assertSame(0, DB::connection('tenant')->table('products')->count());
            $this->assertSame(0, DB::connection('tenant')->table('product_categories')->count());

            $this->runCatalogFlow($tenantB, $dbB, 'SAME-SKU', 'Same Product', 'default');

            TenantTestContext::setTenantContext($tenantB, $dbB);
            $this->assertSame(1, DB::connection('tenant')->table('products')->count());
            $this->assertSame(1, DB::connection('tenant')->table('product_categories')->count());
            $this->assertSame(1, DB::connection('tenant')->table('product_attribute_values')->count());

            TenantTestContext::setTenantContext($tenantA, $dbA);
            $this->assertSame(1, DB::connection('tenant')->table('products')->count());
            $this->assertSame(1, DB::connection('tenant')->table('product_categories')->count());
        } finally {
            TenantTestContext::clearTenantContext();
            $this->cleanupProvisioned($provisioned, $template);

            $globalCountsAfter = $this->captureGlobalCounts($template);
            $this->assertSame($globalCountsBefore, $globalCountsAfter, 'Global DB pollution detected');
        }
    }

    protected function runCatalogFlow(
        Tenant $tenant,
        TenantDatabase $db,
        string $sku,
        string $productName,
        string $categorySlug
    ): void {
        TenantTestContext::setTenantContext($tenant, $db);
        TenantTestContext::resetTenantDatabase($db);
        TenantTestContext::setTenantContext($tenant, $db);

        app(TenantCatalogSeeder::class)->seed();

        $locale = config('app.locale', 'en');

        $conn = DB::connection('tenant');
        $categoryId = $conn->table('categories')->insertGetId([
            'parent_id' => null,
            'status' => 1,
            'position' => 1,
            'display_mode' => 'products_and_description',
            '_lft' => 3,
            '_rgt' => 4,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $conn->table('category_translations')->insert([
            'category_id' => $categoryId,
            'locale' => $locale,
            'name' => 'Test Category',
            'slug' => $categorySlug,
            'url_path' => $categorySlug,
        ]);

        $attributeId = $this->ensureNameAttribute();

        $productId = $conn->table('products')->insertGetId([
            'type' => 'simple',
            'attribute_family_id' => null,
            'sku' => $sku,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $conn->table('product_categories')->insert([
            'product_id' => $productId,
            'category_id' => $categoryId,
        ]);

        if ($attributeId) {
            $conn->table('product_attribute_values')->insert([
                'product_id' => $productId,
                'attribute_id' => $attributeId,
                'locale' => $locale,
                'channel' => 'default',
                'text_value' => $productName,
                'unique_id' => implode('|', array_filter([
                    'default',
                    $locale,
                    $productId,
                    $attributeId,
                ])),
            ]);
        }
    }

    protected function ensureNameAttribute(): ?int
    {
        $conn = DB::connection('tenant');
        $attributeId = $conn->table('attributes')->where('code', 'name')->value('id');

        if ($attributeId) {
            return (int) $attributeId;
        }

        return $conn->table('attributes')->insertGetId([
            'code' => 'name',
            'admin_name' => 'Name',
            'type' => 'text',
            'is_required' => 1,
            'is_unique' => 0,
            'is_filterable' => 0,
            'is_comparable' => 0,
            'is_configurable' => 0,
            'is_user_defined' => 0,
            'is_visible_on_front' => 0,
            'value_per_locale' => 1,
            'value_per_channel' => 0,
            'enable_wysiwyg' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
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
            'seed' => false,
        ]);

        if (! $result['ok']) {
            DB::connection(config('saas.tenant_db.connection_template', 'mysql'))->statement(
                'DROP DATABASE IF EXISTS `' . str_replace('`', '``', $dbName) . '`'
            );
            $tenantDb->delete();
            $tenant->delete();

            $this->markTestSkipped('Provisioning failed: ' . ($result['reason'] ?? 'unknown'));
        }

        $host = Str::slug($dbName, '-') . '.localhost';

        Domain::create([
            'tenant_id' => $tenant->id,
            'domain' => $host,
            'type' => 'subdomain',
            'is_primary' => true,
            'verified_at' => null,
            'created_by_id' => null,
            'note' => 'test',
        ]);

        $tenantDb->refresh();

        return [$tenant, $tenantDb, $host];
    }

    private function captureGlobalCounts(string $connName): array
    {
        $globalConn = DB::connection($connName);

        return [
            'channels' => $globalConn->table('channels')->count(),
            'locales' => $globalConn->table('locales')->count(),
            'currencies' => $globalConn->table('currencies')->count(),
            'categories' => $globalConn->table('categories')->count(),
            'products' => $globalConn->table('products')->count(),
        ];
    }

    private function cleanupProvisioned(array $provisioned, string $template): void
    {
        foreach ($provisioned as [$tenant, $db]) {
            try {
                $db->refresh();

                DB::connection($template)->statement(
                    'DROP DATABASE IF EXISTS `' . str_replace('`', '``', $db->database_name) . '`'
                );

                Domain::where('tenant_id', $tenant->id)->delete();

                $db->delete();
                $tenant->delete();
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }
}
