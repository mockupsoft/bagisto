<?php

namespace Tests\Feature;

use App\Models\Tenant\Domain;
use App\Services\Tenant\DnsTxtResolver;
use App\Services\Tenant\DomainVerificationService;
use App\Services\Tenant\TenantProvisioner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MerchantDomainVerificationTest extends TestCase
{
    use RefreshDatabase;

    private const SESSION_KEY = 'onboarding.merchant_register';

    public function test_instructions_endpoint_returns_dns_and_http_instructions(): void
    {
        $tenant = app(TenantProvisioner::class)->createTenant(['name' => 'Merchant']);
        $domain = app(TenantProvisioner::class)->attachCustomDomain($tenant, 'merchant-custom.example.test');

        $this->withSession([
            self::SESSION_KEY => ['tenant_id' => $tenant->id],
        ])->get(route('merchant.domains.instructions', ['domain' => $domain->id]))
            ->assertOk()
            ->assertJsonPath('domain', 'merchant-custom.example.test')
            ->assertJsonPath('dns_txt.host', DomainVerificationService::DNS_PREFIX . 'merchant-custom.example.test')
            ->assertJsonPath('http_file.url', 'https://merchant-custom.example.test' . DomainVerificationService::HTTP_WELL_KNOWN_PATH);
    }

    public function test_verify_dns_success_via_controller(): void
    {
        $tenant = app(TenantProvisioner::class)->createTenant(['name' => 'Merchant']);
        $domain = app(TenantProvisioner::class)->attachCustomDomain($tenant, 'merchant-custom.example.test');

        $service = new DomainVerificationService(new class($domain) implements DnsTxtResolver {
            public function __construct(private Domain $domain)
            {
            }

            public function getTxtRecords(string $host): array
            {
                return ['saas-verify=' . $this->domain->verification_token];
            }
        });

        app()->instance(DomainVerificationService::class, $service);

        $this->withSession([
            self::SESSION_KEY => ['tenant_id' => $tenant->id],
        ])->postJson(route('merchant.domains.verify', ['domain' => $domain->id]), ['method' => 'dns_txt'])
            ->assertOk()
            ->assertJsonPath('ok', true);

        $this->assertNotNull($domain->refresh()->verified_at);
    }

    public function test_verify_http_success_via_controller(): void
    {
        $tenant = app(TenantProvisioner::class)->createTenant(['name' => 'Merchant']);
        $domain = app(TenantProvisioner::class)->attachCustomDomain($tenant, 'merchant-custom.example.test');

        $service = app(DomainVerificationService::class);
        $domain = $service->start($domain, 'http_file');

        $instruction = $service->getHttpInstruction($domain);
        $altUrl = str_replace('https://', 'http://', $instruction['url']);

        Http::fake([
            $instruction['url'] => Http::response($instruction['value'], 200),
            $altUrl => Http::response($instruction['value'], 200),
        ]);

        $this->withSession([
            self::SESSION_KEY => ['tenant_id' => $tenant->id],
        ])->postJson(route('merchant.domains.verify', ['domain' => $domain->id]), ['method' => 'http_file'])
            ->assertOk()
            ->assertJsonPath('ok', true);

        $this->assertNotNull($domain->refresh()->verified_at);
    }

    public function test_verify_is_rate_limited(): void
    {
        $tenant = app(TenantProvisioner::class)->createTenant(['name' => 'Merchant']);
        $domain = app(TenantProvisioner::class)->attachCustomDomain($tenant, 'merchant-custom.example.test');

        $service = new DomainVerificationService(new class implements DnsTxtResolver {
            public function getTxtRecords(string $host): array
            {
                return [];
            }
        });

        app()->instance(DomainVerificationService::class, $service);

        for ($i = 0; $i < 10; $i++) {
            $this->withSession([
                self::SESSION_KEY => ['tenant_id' => $tenant->id],
            ])->postJson(route('merchant.domains.verify', ['domain' => $domain->id]), ['method' => 'dns_txt'])
                ->assertStatus(422);
        }

        $this->withSession([
            self::SESSION_KEY => ['tenant_id' => $tenant->id],
        ])->postJson(route('merchant.domains.verify', ['domain' => $domain->id]), ['method' => 'dns_txt'])
            ->assertStatus(429)
            ->assertJsonPath('reason', 'rate_limited');
    }
}
