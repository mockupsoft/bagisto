<?php

namespace Tests\Feature;

use App\Models\Tenant\Domain;
use App\Models\Tenant\Tenant;
use App\Models\Tenant\TenantDatabase;
use App\Services\Tenant\DomainVerificationService;
use App\Services\Tenant\TenantProvisioner;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        $domain = $service->attachCustomDomain($tenant, 'beta.example.test');

        $oldToken = $domain->verification_token;

        $domain = $verify->start($domain, 'http_file');

        $this->assertEquals('http_file', $domain->verification_method);
        $this->assertNotNull($domain->verification_started_at);
        $this->assertNotEquals($oldToken, $domain->verification_token);
        $this->assertNull($domain->verified_at);

        $domain = $verify->markVerified($domain);

        $this->assertNotNull($domain->verified_at);
    }
}
