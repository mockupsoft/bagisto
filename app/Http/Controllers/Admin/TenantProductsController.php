<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Tenant;
use App\Models\Tenant\TenantDatabase;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use App\DataGrids\Admin\TenantProductDataGrid;
use Webkul\Product\Repositories\ProductRepository;

class TenantProductsController extends Controller
{
    public function __construct(
        protected ProductRepository $productRepository
    ) {}

    public function index(Tenant $tenant): View|JsonResponse
    {
        $tenantDb = TenantDatabase::where('tenant_id', $tenant->id)->first();

        if (! $tenantDb) {
            abort(404, 'Tenant database not found');
        }

        TenantControllerHelper::setTenantContext($tenant, $tenantDb);

        try {
            if (request()->ajax()) {
                return datagrid(TenantProductDataGrid::class)->process();
            }

            return view('admin.tenants.products', [
                'tenant' => $tenant,
            ]);
        } finally {
            TenantControllerHelper::clearTenantContext();
        }
    }

    public function show(Tenant $tenant, int $productId): View
    {
        $tenantDb = TenantDatabase::where('tenant_id', $tenant->id)->first();

        if (! $tenantDb) {
            abort(404, 'Tenant database not found');
        }

        TenantControllerHelper::setTenantContext($tenant, $tenantDb);

        try {
            $product = $this->productRepository->with(['categories', 'attribute_family', 'product_flats'])->findOrFail($productId);
            
            // Get product flat for current locale/channel
            $productFlat = $product->product_flats()
                ->where('locale', app()->getLocale())
                ->where('channel', core()->getCurrentChannelCode())
                ->first();

            return view('admin.tenants.products.show', [
                'tenant' => $tenant,
                'product' => $product,
                'productFlat' => $productFlat,
            ]);
        } finally {
            TenantControllerHelper::clearTenantContext();
        }
    }
}
