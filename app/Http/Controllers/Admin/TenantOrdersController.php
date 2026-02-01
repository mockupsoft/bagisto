<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Tenant;
use App\Models\Tenant\TenantDatabase;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use App\DataGrids\Admin\TenantOrderDataGrid;
use Webkul\Sales\Repositories\OrderRepository;

class TenantOrdersController extends Controller
{
    public function __construct(
        protected OrderRepository $orderRepository
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
                return datagrid(TenantOrderDataGrid::class)->process();
            }

            return view('admin.tenants.orders', [
                'tenant' => $tenant,
            ]);
        } finally {
            TenantControllerHelper::clearTenantContext();
        }
    }

    public function show(Tenant $tenant, int $orderId): View
    {
        $tenantDb = TenantDatabase::where('tenant_id', $tenant->id)->first();

        if (! $tenantDb) {
            abort(404, 'Tenant database not found');
        }

        TenantControllerHelper::setTenantContext($tenant, $tenantDb);

        try {
            $order = $this->orderRepository->with(['items', 'addresses', 'payment', 'shipments'])->findOrFail($orderId);

            return view('admin.tenants.orders.show', [
                'tenant' => $tenant,
                'order' => $order,
            ]);
        } finally {
            TenantControllerHelper::clearTenantContext();
        }
    }
}
