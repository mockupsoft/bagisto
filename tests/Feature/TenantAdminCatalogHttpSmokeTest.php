<?php

namespace Tests\Feature;

use App\Models\Tenant\Domain;
use App\Models\Tenant\Tenant;
use App\Models\Tenant\TenantDatabase;
use App\Services\Tenant\TenantCatalogSeeder;
use App\Services\Tenant\TenantConnectionConfigurator;
use App\Services\Tenant\TenantDatabaseProvisioner;
use App\Services\Tenant\TenantResolver;
use App\Support\Tenant\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Tests\Support\TenantTestContext;
use Tests\TestCase;

class TenantAdminCatalogHttpSmokeTest extends TestCase
{
    protected array $connectionsToTransact = [];

    public function test_admin_request_persists_catalog_data_in_tenant_db(): void
    {
        if (! env('RUN_TENANT_DDL_TESTS', false)) {
            $this->markTestSkipped('RUN_TENANT_DDL_TESTS env not set');
        }

        $template = config('saas.tenant_db.connection_template', 'mysql');
        $globalCountsBefore = $this->captureGlobalCounts($template);
        $provisioned = [];

        [$tenant, $db, $host] = $this->provisionTenantWithDb('tenant_admin_catalog');
        $provisioned[] = [$tenant, $db];

        try {
            TenantTestContext::setTenantContext($tenant, $db);
            app(TenantCatalogSeeder::class)->seed();
            $this->ensureNameAttribute();
            TenantTestContext::clearTenantContext();

            $this->registerSmokeRoute();

            $this->withoutMiddleware(VerifyCsrfToken::class);

            $payload = [
                'sku' => 'HTTP-SKU-1',
                'category' => 'Http Category',
                'product' => 'Http Product',
            ];

            $response = $this->post('http://' . $host . $this->smokePath(), $payload);

            $response->assertOk();
            $response->assertJson(['ok' => true]);

            $slug = Str::slug($payload['category']);

            TenantTestContext::setTenantContext($tenant, $db);

            $this->assertSame(1, DB::connection('tenant')->table('products')->where('sku', $payload['sku'])->count());
            $this->assertSame(1, DB::connection('tenant')->table('category_translations')->where('slug', $slug)->count());
            $this->assertSame(1, DB::connection('tenant')->table('product_categories')->count());
            $this->assertSame(1, DB::connection('tenant')->table('product_attribute_values')->where('text_value', $payload['product'])->count());

            $this->assertSame(0, DB::connection($template)->table('products')->where('sku', $payload['sku'])->count());
            $this->assertSame(0, DB::connection($template)->table('category_translations')->where('slug', $slug)->count());
        } finally {
            TenantTestContext::clearTenantContext();
            $this->cleanupProvisioned($provisioned, $template);

            $globalCountsAfter = $this->captureGlobalCounts($template);
            $this->assertSame($globalCountsBefore, $globalCountsAfter, 'Global DB pollution detected');
        }
    }

    protected function registerSmokeRoute(): void
    {
        if (Route::has('admin.tenant.catalog.smoke')) {
            return;
        }

        Route::middleware(['web'])->post($this->smokePath(), function (
            Request $request,
            TenantResolver $resolver,
            TenantConnectionConfigurator $configurator,
            TenantContext $context
        ) {
            $payload = $request->validate([
                'sku' => ['required', 'string'],
                'category' => ['required', 'string'],
                'product' => ['required', 'string'],
            ]);

            $resolved = $resolver->resolveByHost($request->getHost());

            if (! $resolved) {
                return response()->json(['ok' => false, 'reason' => 'tenant'], 404);
            }

            $tenant = $resolved['tenant'];
            $domain = $resolved['domain'];

            $dbMeta = TenantDatabase::where('tenant_id', $tenant->id)->whereNull('deleted_at')->first();

            if (! $dbMeta) {
                return response()->json(['ok' => false, 'reason' => 'db'], 503);
            }

            $configurator->configure($dbMeta);
            DB::purge('tenant');
            DB::reconnect('tenant');

            $context->setTenant($tenant);
            $context->setDomain($domain);

            $conn = DB::connection('tenant');
            $now = now();
            $maxRight = (int) $conn->table('categories')->max('_rgt');
            $left = $maxRight > 0 ? $maxRight + 1 : 1;
            $right = $left + 1;
            $slug = Str::slug($payload['category']);

            $categoryId = $conn->table('categories')->insertGetId([
                'parent_id' => null,
                'status' => 1,
                'position' => 1,
                'display_mode' => 'products_and_description',
                '_lft' => $left,
                '_rgt' => $right,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $conn->table('category_translations')->insert([
                'category_id' => $categoryId,
                'locale' => config('app.locale', 'en'),
                'name' => $payload['category'],
                'slug' => $slug,
                'url_path' => $slug,
            ]);

            $productId = $conn->table('products')->insertGetId([
                'type' => 'simple',
                'attribute_family_id' => null,
                'sku' => $payload['sku'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $conn->table('product_categories')->insert([
                'product_id' => $productId,
                'category_id' => $categoryId,
            ]);

            $attributeId = $conn->table('attributes')->where('code', 'name')->value('id');

            if ($attributeId) {
                $conn->table('product_attribute_values')->insert([
                    'product_id' => $productId,
                    'attribute_id' => $attributeId,
                    'locale' => config('app.locale', 'en'),
                    'channel' => 'default',
                    'text_value' => $payload['product'],
                    'unique_id' => implode('|', array_filter([
                        'default',
                        config('app.locale', 'en'),
                        $productId,
                        $attributeId,
                    ])),
                ]);
            }

            return response()->json([
                'ok' => true,
                'category_id' => $categoryId,
                'product_id' => $productId,
            ]);
        })->name('admin.tenant.catalog.smoke');
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

    protected function smokePath(): string
    {
        $prefix = trim(config('app.admin_url'), '/');

        return '/' . $prefix . '/tenant-catalog-smoke';
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
