<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Tenant;
use App\Models\Tenant\TenantDatabase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\DataGrids\Admin\TenantCustomerDataGrid;
use Webkul\Customer\Repositories\CustomerRepository;
use Webkul\Customer\Models\Customer;

class TenantCustomersController extends Controller
{
    public function __construct(
        protected CustomerRepository $customerRepository
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
                return datagrid(TenantCustomerDataGrid::class)->process();
            }

            return view('admin.tenants.customers', [
                'tenant' => $tenant,
            ]);
        } finally {
            TenantControllerHelper::clearTenantContext();
        }
    }

    public function show(Tenant $tenant, int $customerId): View
    {
        $tenantDb = TenantDatabase::where('tenant_id', $tenant->id)->first();

        if (! $tenantDb) {
            abort(404, 'Tenant database not found');
        }

        TenantControllerHelper::setTenantContext($tenant, $tenantDb);

        try {
            $customer = $this->customerRepository->with(['addresses', 'group', 'orders'])->findOrFail($customerId);

            return view('admin.tenants.customers.show', [
                'tenant' => $tenant,
                'customer' => $customer,
            ]);
        } finally {
            TenantControllerHelper::clearTenantContext();
        }
    }

    public function suspend(Tenant $tenant, int $customerId): RedirectResponse
    {
        $tenantDb = TenantDatabase::where('tenant_id', $tenant->id)->first();

        if (! $tenantDb) {
            abort(404, 'Tenant database not found');
        }

        TenantControllerHelper::setTenantContext($tenant, $tenantDb);

        try {
            $customer = $this->customerRepository->findOrFail($customerId);
            
            $this->customerRepository->update([
                'is_suspended' => true,
            ], $customerId);

            return redirect()
                ->route('admin.tenants.customers.show', [$tenant->id, $customerId])
                ->with('success', trans('admin::app.customers.customers.update-success'));
        } finally {
            TenantControllerHelper::clearTenantContext();
        }
    }

    public function activate(Tenant $tenant, int $customerId): RedirectResponse
    {
        $tenantDb = TenantDatabase::where('tenant_id', $tenant->id)->first();

        if (! $tenantDb) {
            abort(404, 'Tenant database not found');
        }

        TenantControllerHelper::setTenantContext($tenant, $tenantDb);

        try {
            $customer = $this->customerRepository->findOrFail($customerId);
            
            $this->customerRepository->update([
                'is_suspended' => false,
                'status' => true,
            ], $customerId);

            return redirect()
                ->route('admin.tenants.customers.show', [$tenant->id, $customerId])
                ->with('success', trans('admin::app.customers.customers.update-success'));
        } finally {
            TenantControllerHelper::clearTenantContext();
        }
    }
}
