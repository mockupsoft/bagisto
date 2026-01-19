<?php

namespace Tests\Feature;

use App\Models\Tenant\Domain;
use App\Models\Tenant\Tenant;
use App\Services\Tenant\DnsTxtResolver;
use App\Services\Tenant\DomainVerificationService;
use App\Services\Tenant\TenantProvisioner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class DomainVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_attach_custom_domain_creates_token_and_method(): void
    {
        $service = app(TenantProvisioner::class);

        $tenant = $service->createTenant(['name' => 'Acme']);

        $domain = $service->attachCustomDomain($tenant, 'custom.example.test');

        $this->assertNull($domain->verified_at);
        $this->assertEquals('dns_txt', $domain->verification_method);
        $this->assertNotEmpty($domain->verification_token);
        $this->assertTrue(strlen($domain->verification_token) >= 20);
    }

    public function test_start_and_mark_verified(): void
    {
        $service = app(TenantProvisioner::class);
        $verify = app(DomainVerificationService::class);

        $tenant = $service->createTenant(['name' => 'Beta']);
        $domain = $service->attachCustomDomain($tenant, 'beta-custom.example.test');

        $oldToken = $domain->verification_token;

        $domain = $verify->start($domain, 'http_file');

        $this->assertEquals('http_file', $domain->verification_method);
        $this->assertNotNull($domain->verification_started_at);
        $this->assertNotEquals($oldToken, $domain->verification_token);
        $this->assertNull($domain->verified_at);

        $domain = $verify->markVerified($domain);

        $this->assertNotNull($domain->verified_at);
    }

    public function test_dns_verify_success_and_failure_are_recorded(): void
    {
        RateLimiter::clear('domain-verify:domain:1');

        $service = app(TenantProvisioner::class);

        $tenant = $service->createTenant(['name' => 'DnsTenant']);
        $domain = $service->attachCustomDomain($tenant, 'example.test');

        $verify = new DomainVerificationService(new class implements DnsTxtResolver {
            public function getTxtRecords(string $host): array
            {
                return ['saas-verify=wrong'];
            }
        });

        $resultFail = $verify->attemptVerify($domain, 'dns_txt');

        $this->assertFalse($resultFail['ok']);
        $this->assertSame('dns_txt_mismatch', $resultFail['reason']);

        $domain->refresh();
        $this->assertNotNull($domain->last_checked_at);
        $this->assertSame('dns_txt_mismatch', $domain->last_failure_reason);
        $this->assertNull($domain->verified_at);

        $verifyOk = new DomainVerificationService(new class($domain) implements DnsTxtResolver {
            public function __construct(private Domain $domain)
            {
            }

            public function getTxtRecords(string $host): array
            {
                return ['saas-verify=' . $this->domain->verification_token];
            }
        });

        $resultOk = $verifyOk->attemptVerify($domain->refresh(), 'dns_txt');

        $this->assertTrue($resultOk['ok']);

        $domain->refresh();
        $this->assertNotNull($domain->verified_at);
        $this->assertNull($domain->last_failure_reason);
    }

    public function test_http_verify_success_and_failure(): void
    {
        $service = app(TenantProvisioner::class);

        $tenant = $service->createTenant(['name' => 'HttpTenant']);
        $domain = $service->attachCustomDomain($tenant, 'example.test');

        $verify = app(DomainVerificationService::class);
        $domain = $verify->start($domain, 'http_file');

        $instruction = $verify->getHttpInstruction($domain);
        $altUrl = str_replace('https://', 'http://', $instruction['url']);

        Http::fake([
            $instruction['url'] => Http::response('wrong', 200),
            $altUrl => Http::response('wrong', 200),
        ]);

        $fail = $verify->attemptVerify($domain, 'http_file');
        $this->assertFalse($fail['ok']);

        Http::fake([
            $instruction['url'] => Http::response($instruction['value'], 200),
            $altUrl => Http::response($instruction['value'], 200),
        ]);

        $ok = $verify->attemptVerify($domain->refresh(), 'http_file');
        $this->assertTrue($ok['ok']);
        $this->assertNotNull($domain->refresh()->verified_at);
    }

    public function test_verified_domain_cannot_be_claimed_by_another_tenant(): void
    {
        $provisioner = app(TenantProvisioner::class);
        $verify = app(DomainVerificationService::class);

        $tenantA = $provisioner->createTenant(['name' => 'Owner']);
        $domainA = $provisioner->attachCustomDomain($tenantA, 'claimed.example.test');
        $verify->markVerified($domainA);

        $tenantB = $provisioner->createTenant(['name' => 'Attacker']);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('domain_already_verified');

        $provisioner->attachCustomDomain($tenantB, 'claimed.example.test');
    }
}
