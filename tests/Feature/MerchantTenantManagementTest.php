<?php

namespace Tests\Feature;

use App\Models\MerchantUser;
use App\Services\Tenant\DomainVerificationService;
use App\Services\Tenant\TenantProvisioner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MerchantTenantManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_merchant_can_view_dashboard_for_own_tenant(): void
    {
        $provisioner = app(TenantProvisioner::class);
        $tenant = $provisioner->createTenant(['name' => 'Acme', 'status' => 'active']);

        $merchant = MerchantUser::create([
            'tenant_id' => $tenant->id,
            'name' => 'Merchant',
            'email' => 'merchant@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $response = $this->actingAs($merchant, 'merchant')->get(route('merchant.dashboard'));

        $response->assertOk();
        $response->assertSee((string) $tenant->id);
    }

    public function test_merchant_cannot_rotate_token_for_other_tenant_domain(): void
    {
        $provisioner = app(TenantProvisioner::class);
        $tenantA = $provisioner->createTenant(['name' => 'A', 'status' => 'active']);
        $tenantB = $provisioner->createTenant(['name' => 'B', 'status' => 'active']);

        $domainB = $provisioner->attachCustomDomain($tenantB, 'b.example.test');

        $merchantA = MerchantUser::create([
            'tenant_id' => $tenantA->id,
            'name' => 'Merchant',
            'email' => 'merchant-a@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $this->actingAs($merchantA, 'merchant')
            ->post(route('merchant.domains.rotate', ['domain' => $domainB->id]))
            ->assertStatus(403);
    }

    public function test_merchant_can_add_domain_and_see_instructions(): void
    {
        $provisioner = app(TenantProvisioner::class);
        $tenant = $provisioner->createTenant(['name' => 'Acme', 'status' => 'active']);

        $merchant = MerchantUser::create([
            'tenant_id' => $tenant->id,
            'name' => 'Merchant',
            'email' => 'merchant@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $this->actingAs($merchant, 'merchant')
            ->post(route('merchant.domains.add'), ['domain' => 'custom.example.test'])
            ->assertRedirect(route('merchant.dashboard'));

        $dashboard = $this->actingAs($merchant, 'merchant')->get(route('merchant.dashboard'));

        $dashboard->assertOk();
        $dashboard->assertSee('_saas-verify.custom.example.test');
        $dashboard->assertSee('/.well-known/saas-domain-verification.txt');
    }

    public function test_merchant_verify_now_handles_http_fake(): void
    {
        $provisioner = app(TenantProvisioner::class);
        $tenant = $provisioner->createTenant(['name' => 'Acme', 'status' => 'active']);

        $merchant = MerchantUser::create([
            'tenant_id' => $tenant->id,
            'name' => 'Merchant',
            'email' => 'merchant@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $domain = $provisioner->attachCustomDomain($tenant, 'http.example.test');

        $service = app(DomainVerificationService::class);
        $service->start($domain, DomainVerificationService::METHOD_HTTP_FILE);
        $instruction = $service->getHttpInstruction($domain->refresh());

        Http::fake([
            $instruction['url'] => Http::response($instruction['value'], 200),
        ]);

        $this->actingAs($merchant, 'merchant')
            ->post(route('merchant.domains.verify', ['domain' => $domain->id]), ['method' => 'http_file'])
            ->assertRedirect(route('merchant.dashboard'));

        $this->assertNotNull($domain->refresh()->verified_at);
    }
}
