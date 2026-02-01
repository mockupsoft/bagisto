<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Tenant;
use App\Models\Tenant\TenantDatabase;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use App\DataGrids\Admin\TenantAttributeFamilyDataGrid;

class TenantAttributeFamiliesController extends Controller
{
    public function index(Tenant $tenant): View|JsonResponse
    {
        $tenantDb = TenantDatabase::where('tenant_id', $tenant->id)->first();

        if (! $tenantDb) {
            abort(404, 'Tenant database not found');
        }

        TenantControllerHelper::setTenantContext($tenant, $tenantDb);

        try {
            if (request()->ajax()) {
                return datagrid(TenantAttributeFamilyDataGrid::class)->process();
            }

            return view('admin.tenants.attribute-families.index', [
                'tenant' => $tenant,
            ]);
        } finally {
            TenantControllerHelper::clearTenantContext();
        }
    }
}
