<?php

namespace Tests\Feature;

use App\Services\Tenant\TenantConnectionSelector;
use App\Support\Tenant\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Webkul\Product\Models\Product;
use App\Models\Tenant\Tenant;

class TenantRouterCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_model_uses_tenant_connection_when_tenant_resolved(): void
    {
        $context = app(TenantContext::class);
        $context->clear();

        $tenant = Tenant::create([
            'name' => 'Acme',
            'slug' => 'acme',
            'status' => 'active',
        ]);

        $context->setTenant($tenant);

        $product = new Product();
        app(TenantConnectionSelector::class)->apply($product);

        $this->assertEquals('tenant', $product->getConnectionName());
    }

    public function test_catalog_model_stays_global_when_no_tenant(): void
    {
        $context = app(TenantContext::class);
        $context->clear();

        $product = new Product();
        app(TenantConnectionSelector::class)->apply($product);

        $this->assertNull($product->getConnectionName());
    }
}
