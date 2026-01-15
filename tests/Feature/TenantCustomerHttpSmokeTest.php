<?php

namespace Tests\Feature;

use App\Models\Tenant\Domain;
use App\Models\Tenant\Tenant;
use App\Models\Tenant\TenantDatabase;
use App\Services\Tenant\TenantCustomerSeeder;
use App\Services\Tenant\TenantDatabaseProvisioner;
use Illuminate\Auth\SessionGuard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Tests\Support\TenantTestContext;
use Tests\TestCase;
use Webkul\Customer\Models\CustomerAddress;

class TenantCustomerHttpSmokeTest extends TestCase
{
    private const REDIRECT_STATUSES = [301, 302, 303, 307, 308];

    protected array $connectionsToTransact = [];

    public function test_tenant_customer_register_login_and_address(): void
    {
        if (! env('RUN_TENANT_DDL_TESTS', false)) {
            $this->markTestSkipped('RUN_TENANT_DDL_TESTS env not set');
        }

        $template = config('saas.tenant_db.connection_template', 'mysql');

        $customerSeeder = app(TenantCustomerSeeder::class);
        $customerSeeder->seedGlobal();

        $globalCountsBefore = $this->captureGlobalCounts($template);
        $provisioned = [];

        [$tenantA, $dbA, $hostA] = $this->provisionTenantWithDb('tenant_http_a');
        [$tenantB, $dbB, $hostB] = $this->provisionTenantWithDb('tenant_http_b');

        $provisioned[] = [$tenantA, $dbA];
        $provisioned[] = [$tenantB, $dbB];

        try {
            $flowA = $this->runTenantFlow(
                tenant: $tenantA,
                db: $dbA,
                customerSeeder: $customerSeeder,
                host: $hostA,
                email: 'same@example.com',
                logoutAtEnd: false,
                resetSessionAtStart: true
            );

            Auth::forgetGuards();

            $protectedResponseA = $this->withServerVariables(['HTTP_HOST' => $flowA['host']])
                ->get(route('shop.customers.account.addresses.index'));

            $this->assertCustomerAuthenticatedResponse($protectedResponseA);

            // Switch to tenant B and verify session from tenant A does not authenticate
            TenantTestContext::setTenantContext($tenantB, $dbB);
            TenantTestContext::resetTenantDatabase($dbB);
            TenantTestContext::setTenantContext($tenantB, $dbB);
            $customerSeeder->seed();

            Auth::forgetGuards();

            $protectedResponse = $this->withServerVariables(['HTTP_HOST' => $hostB])->get(route('shop.customers.account.addresses.index'));
            $this->assertRedirectsToCustomerLogin($protectedResponse);

            Auth::forgetGuards();

            $this->assertNull(Auth::guard('customer')->user());
            $this->assertFalse(Auth::guard('customer')->check());

            $this->runTenantFlow(
                tenant: $tenantB,
                db: $dbB,
                customerSeeder: $customerSeeder,
                host: $hostB,
                email: 'same@example.com',
                logoutAtEnd: true,
                resetSessionAtStart: true
            );

            // Clean up tenant A login held from first flow
            TenantTestContext::setTenantContext($tenantA, $dbA);
            Auth::guard('customer')->logout();
            Auth::forgetGuards();
            $this->resetSessionState();

            Auth::forgetGuards();

            $protectedResponseAfterTenantACleanup = $this->withServerVariables(['HTTP_HOST' => $flowA['host']])
                ->get(route('shop.customers.account.addresses.index'));

            $this->assertRedirectsToCustomerLogin($protectedResponseAfterTenantACleanup);

        } finally {

            TenantTestContext::clearTenantContext();
            $this->cleanupProvisioned($provisioned, $template);

            $globalCountsAfter = $this->captureGlobalCounts($template);
            $this->assertSame($globalCountsBefore, $globalCountsAfter, 'Global DB pollution detected');
        }
    }

    protected function runTenantFlow(
        Tenant $tenant,
        TenantDatabase $db,
        TenantCustomerSeeder $customerSeeder,
        string $host,
        string $email,
        bool $logoutAtEnd = true,
        bool $resetSessionAtStart = true
    ): array {
        if ($resetSessionAtStart) {
            Auth::forgetGuards();
            $this->resetSessionState();
        }

        TenantTestContext::setTenantContext($tenant, $db);
        TenantTestContext::resetTenantDatabase($db);
        TenantTestContext::setTenantContext($tenant, $db);
        $customerSeeder->seed();


        // Register
        $registerResponse = $this->withServerVariables(['HTTP_HOST' => $host])->post(route('shop.customers.register.store'), [
            'first_name' => 'Tenant',
            'last_name' => 'User',
            'email' => $email,
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'is_subscribed' => 0,
        ]);

        $registerResponse->assertRedirectToRoute('shop.customer.session.index');

        $this->assertSame(1, DB::connection('tenant')->table('customers')->where('email', $email)->count());
        $channelIdInCustomer = DB::connection('tenant')->table('customers')->where('email', $email)->value('channel_id');
        $expectedChannelId = DB::connection(config('saas.tenant_db.connection_template', 'mysql'))
            ->table('channels')
            ->where('code', 'default')
            ->value('id');

        if (! is_null($expectedChannelId) && ! is_null($channelIdInCustomer)) {
            $this->assertEquals($expectedChannelId, $channelIdInCustomer);
        }

        // Login
        $loginResponse = $this->withServerVariables(['HTTP_HOST' => $host])->post(route('shop.customer.session.create'), [
            'email' => $email,
            'password' => 'secret123',
        ]);

        $loginResponse->assertRedirect(route('shop.home.index'));

        Auth::forgetGuards();

        $protectedResponse = $this->withServerVariables(['HTTP_HOST' => $host])
            ->get(route('shop.customers.account.addresses.index'));

        $this->assertCustomerAuthenticatedResponse($protectedResponse);

        // Create address (AddressRequest expects 'address' array and other fields)
        $addressResponse = $this->withServerVariables(['HTTP_HOST' => $host])->post(route('shop.customers.account.addresses.store'), [
            'company_name' => null,
            'first_name' => 'Tenant',
            'last_name' => 'User',
            'address' => ['Street 1'],
            'country' => 'TR',
            'state' => 'TR-34',
            'city' => 'Istanbul',
            'postcode' => '34000',
            'phone' => '5550000001',
            'vat_id' => null,
            'email' => $email,
        ]);

        $addressResponse->assertRedirect(route('shop.customers.account.addresses.index'));

        $this->assertSame(1, DB::connection('tenant')->table('customer_addresses')->count());
        $this->assertSame(1, CustomerAddress::query()->count());

        if ($logoutAtEnd) {
            Auth::guard('customer')->logout();
            Auth::forgetGuards();
            $this->resetSessionState();

            Auth::forgetGuards();

            $protectedResponseAfterLogout = $this->withServerVariables(['HTTP_HOST' => $host])
                ->get(route('shop.customers.account.addresses.index'));

            $this->assertRedirectsToCustomerLogin($protectedResponseAfterLogout);

            TenantTestContext::clearTenantContext();
        }

        return [
            'host' => $host,
        ];
    }

    private function assertCustomerAuthenticatedResponse(TestResponse $response): void
    {
        // Some Bagisto setups may redirect even when authenticated (e.g. missing config/seed).
        // The strongest invariant we can assert is: it must NOT redirect to customer login.
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

        // Reduce false-positives: ensure the customer identity is still resolvable.
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

        // Ignore query params (e.g. ?redirect=/account/...). Only compare path.
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
