<?php

namespace Tests\Feature;

use App\Models\Tenant\Domain;
use App\Models\Tenant\Tenant;
use App\Models\Tenant\TenantDatabase;
use App\Services\Tenant\TenantCustomerSeeder;
use App\Services\Tenant\TenantDatabaseProvisioner;
use App\Services\Tenant\TenantSalesSeeder;
use Illuminate\Auth\SessionGuard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Tests\Support\TenantTestContext;
use Tests\TestCase;

class TenantSalesCheckoutSmokeTest extends TestCase
{
    private const REDIRECT_STATUSES = [301, 302, 303, 307, 308];

    protected array $connectionsToTransact = [];

    public function test_tenant_customer_can_checkout_and_orders_are_isolated(): void
    {
        if (! env('RUN_TENANT_DDL_TESTS', false)) {
            $this->markTestSkipped('RUN_TENANT_DDL_TESTS env not set');
        }

        $template = config('saas.tenant_db.connection_template', 'mysql');

        $customerSeeder = app(TenantCustomerSeeder::class);
        $customerSeeder->seedGlobal();

        $globalCountsBefore = $this->captureGlobalCounts($template);
        $provisioned = [];

        [$tenantA, $dbA, $hostA] = $this->provisionTenantWithDb('tenant_sales_a');
        [$tenantB, $dbB, $hostB] = $this->provisionTenantWithDb('tenant_sales_b');

        $provisioned[] = [$tenantA, $dbA];
        $provisioned[] = [$tenantB, $dbB];

        try {
            $this->runCheckoutFlow(
                tenant: $tenantA,
                db: $dbA,
                host: $hostA,
                email: 'buyer@example.com',
                sku: 'SAME-SKU'
            );

            // Tenant A should have exactly 1 order.
            TenantTestContext::setTenantContext($tenantA, $dbA);
            $this->assertSame(1, DB::connection('tenant')->table('orders')->count());
            $this->assertSame(1, DB::connection('tenant')->table('order_items')->count());

            // Switch to tenant B and confirm there is no order pollution.
            TenantTestContext::setTenantContext($tenantB, $dbB);
            $this->assertSame(0, DB::connection('tenant')->table('orders')->count());
            $this->assertSame(0, DB::connection('tenant')->table('order_items')->count());

            // Tenant B should be able to checkout the "same" SKU independently.
            $this->runCheckoutFlow(
                tenant: $tenantB,
                db: $dbB,
                host: $hostB,
                email: 'buyer@example.com',
                sku: 'SAME-SKU'
            );

            TenantTestContext::setTenantContext($tenantB, $dbB);
            $this->assertSame(1, DB::connection('tenant')->table('orders')->count());
            $this->assertSame(1, DB::connection('tenant')->table('order_items')->count());

            // Cross-tenant auth isolation: tenant A session must not authenticate on tenant B.
            Auth::forgetGuards();

            $protectedResponse = $this->withServerVariables(['HTTP_HOST' => $hostB])
                ->get(route('shop.customers.account.orders.index'));

            $this->assertRedirectsToCustomerLogin($protectedResponse);

        } finally {
            TenantTestContext::clearTenantContext();
            $this->cleanupProvisioned($provisioned, $template);

            $globalCountsAfter = $this->captureGlobalCounts($template);
            $this->assertSame($globalCountsBefore, $globalCountsAfter, 'Global DB pollution detected');
        }
    }

    protected function runCheckoutFlow(Tenant $tenant, TenantDatabase $db, string $host, string $email, string $sku): void
    {
        Auth::forgetGuards();
        $this->resetSessionState();

        TenantTestContext::setTenantContext($tenant, $db);
        TenantTestContext::resetTenantDatabase($db);
        TenantTestContext::setTenantContext($tenant, $db);

        app(TenantCustomerSeeder::class)->seed();
        app(TenantSalesSeeder::class)->seed();

        $channelId = DB::connection(config('saas.tenant_db.connection_template', 'mysql'))
            ->table('channels')
            ->where('code', 'default')
            ->value('id');

        $this->assertNotNull($channelId, 'Missing global default channel');

        $attributeFamilyId = DB::connection(config('saas.tenant_db.connection_template', 'mysql'))
            ->table('attribute_families')
            ->value('id');

        $this->assertNotNull($attributeFamilyId, 'Missing global attribute family');

        $productId = DB::connection('tenant')->table('products')->insertGetId([
            'type' => 'simple',
            'attribute_family_id' => $attributeFamilyId,
            'sku' => $sku,
            'parent_id' => null,
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::connection('tenant')->table('product_flat')->insert([
            'product_id' => $productId,
            'sku' => $sku,
            'name' => 'Test Product',
            'price' => 10.0,
            'status' => 1,
            'url_key' => 'test-product-' . Str::lower(Str::random(6)),
            'locale' => 'en',
            'channel' => 'default',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::connection('tenant')->table('product_channels')->insert([
            'product_id' => $productId,
            'channel_id' => $channelId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $inventorySourceId = DB::connection('tenant')->table('inventory_sources')->where('code', 'default')->value('id');
        $this->assertNotNull($inventorySourceId, 'Inventory source seed missing');

        DB::connection('tenant')->table('product_inventories')->updateOrInsert(
            [
                'product_id' => $productId,
                'inventory_source_id' => $inventorySourceId,
                'vendor_id' => 0,
            ],
            ['qty' => 10]
        );

        DB::connection('tenant')->table('product_inventory_indices')->updateOrInsert(
            [
                'product_id' => $productId,
                'channel_id' => $channelId,
            ],
            [
                'qty' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Register
        $registerResponse = $this->withServerVariables(['HTTP_HOST' => $host])->post(route('shop.customers.register.store'), [
            'first_name' => 'Tenant',
            'last_name' => 'Buyer',
            'email' => $email,
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'is_subscribed' => 0,
        ]);

        $registerResponse->assertRedirectToRoute('shop.customer.session.index');

        // Login
        $loginResponse = $this->withServerVariables(['HTTP_HOST' => $host])->post(route('shop.customer.session.create'), [
            'email' => $email,
            'password' => 'secret123',
        ]);

        $loginResponse->assertRedirect(route('shop.home.index'));

        Auth::forgetGuards();

        $this->assertCustomerAuthenticatedResponse(
            $this->withServerVariables(['HTTP_HOST' => $host])->get(route('shop.customers.account.orders.index'))
        );

        // Add to cart.
        $addToCartResponse = $this->withServerVariables(['HTTP_HOST' => $host])->postJson(route('shop.api.checkout.cart.store'), [
            'product_id' => $productId,
            'quantity' => '1',
            'is_buy_now' => '0',
            'rating' => '0',
        ]);

        $addToCartResponse->assertOk();

        // Store addresses.
        $addressPayload = [
            'first_name' => 'Tenant',
            'last_name' => 'Buyer',
            'email' => $email,
            'address' => ['Street 1'],
            'city' => 'Istanbul',
            'state' => 'TR-34',
            'country' => 'TR',
            'postcode' => '34000',
            'phone' => '5550000001',
            'use_for_shipping' => 1,
        ];

        $addressResponse = $this->withServerVariables(['HTTP_HOST' => $host])->postJson(route('shop.checkout.onepage.addresses.store'), [
            'billing' => $addressPayload,
        ]);

        $addressResponse->assertOk();

        // Store shipping method.
        $shippingResponse = $this->withServerVariables(['HTTP_HOST' => $host])->postJson(route('shop.checkout.onepage.shipping_methods.store'), [
            'shipping_method' => 'free_free',
        ]);

        $shippingResponse->assertOk();

        // Store payment method.
        $paymentResponse = $this->withServerVariables(['HTTP_HOST' => $host])->postJson(route('shop.checkout.onepage.payment_methods.store'), [
            'payment' => [
                'method' => 'cashondelivery',
                'method_title' => 'Cash On Delivery',
                'description' => 'Cash On Delivery',
                'sort' => 1,
            ],
        ]);

        $paymentResponse->assertOk();

        // Place order.
        $orderResponse = $this->withServerVariables(['HTTP_HOST' => $host])->postJson(route('shop.checkout.onepage.orders.store'));

        $orderResponse->assertOk();
    }

    private function assertCustomerAuthenticatedResponse(TestResponse $response): void
    {
        if ($response->isRedirect()) {
            $location = $response->headers->get('Location');
            $this->assertNotNull($location, 'Redirect response missing Location header');

            $expectedPath = $this->expectedCustomerLoginPath();
            $actualPath = $this->normalizePath($location);

            $this->assertNotSame(
                $expectedPath,
                $actualPath,
                sprintf(
                    'Expected authenticated customer, but got redirected to login. status=%d location=%s expectedPath=%s actualPath=%s',
                    $response->getStatusCode(),
                    $location,
                    $expectedPath,
                    $actualPath
                )
            );
        } else {
            $response->assertSuccessful();
        }

        Auth::forgetGuards();

        $customerGuard = Auth::guard('customer');
        $customerUser = $customerGuard->user();

        if ($customerUser !== null) {
            return;
        }

        if ($customerGuard instanceof SessionGuard && $this->app->bound('session')) {
            $guardSessionKey = $customerGuard->getName();
            $this->assertTrue(
                $this->app['session']->has($guardSessionKey),
                'Expected authenticated customer session key to exist'
            );

            return;
        }

        $this->assertNotNull($customerUser, 'Expected authenticated customer user to be resolvable');
    }

    private function assertRedirectsToCustomerLogin(TestResponse $response): void
    {
        $this->assertTrue($response->isRedirect(), 'Expected a redirect response');

        $statusCode = $response->getStatusCode();
        $this->assertContains(
            $statusCode,
            self::REDIRECT_STATUSES,
            sprintf('Unexpected redirect status. status=%d location=%s', $statusCode, (string) $response->headers->get('Location'))
        );

        $location = $response->headers->get('Location');
        $this->assertNotNull($location, 'Redirect response missing Location header');

        $expectedPath = $this->expectedCustomerLoginPath();
        $actualPath = $this->normalizePath($location);

        $this->assertSame(
            $expectedPath,
            $actualPath,
            sprintf(
                'Expected redirect to customer login. status=%d location=%s expectedPath=%s actualPath=%s',
                $statusCode,
                $location,
                $expectedPath,
                $actualPath
            )
        );
    }

    private function expectedCustomerLoginPath(): string
    {
        return $this->normalizePath(route('shop.customer.session.index', [], false));
    }

    private function normalizePath(string $urlOrPath): string
    {
        $path = parse_url($urlOrPath, PHP_URL_PATH) ?: $urlOrPath;

        return str_starts_with($path, '/') ? $path : '/' . $path;
    }

    private function resetSessionState(): void
    {
        if (! $this->app->bound('session')) {
            return;
        }

        $session = $this->app['session'];

        $session->flush();
        $session->invalidate();
        $session->regenerateToken();
    }

    private function captureGlobalCounts(string $connName): array
    {
        $globalConn = DB::connection($connName);

        return [
            'channels' => $globalConn->table('channels')->count(),
            'locales' => $globalConn->table('locales')->count(),
            'currencies' => $globalConn->table('currencies')->count(),
            'categories' => $globalConn->table('categories')->count(),
        ];
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
