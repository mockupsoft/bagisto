<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateTenantRequest;
use App\Jobs\ProvisionTenantJob;
use App\Models\MerchantUser;
use App\Models\Tenant\Domain;
use App\Models\Tenant\Tenant;
use App\Services\Tenant\DomainVerificationService;
use App\Services\Tenant\TenantCreateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Queue;
use Illuminate\View\View;

class TenantManagementController extends Controller
{
    public function __construct(
        protected TenantCreateService $tenantCreateService
    ) {
    }
    public function index()
    {
        if (request()->ajax()) {
            return datagrid(\App\DataGrids\Admin\TenantDataGrid::class)->process();
        }

        return view('admin.tenants.index');
    }

    public function create(): View
    {
        return view('admin.tenants.create');
    }

    public function store(CreateTenantRequest $request): RedirectResponse
    {
        try {
            $tenant = $this->tenantCreateService->create($request->validated());

            $message = $request->input('provision_now', true)
                ? trans('admin::app.tenants.create.success-provisioning')
                : trans('admin::app.tenants.create.success');

            return redirect()
                ->route('admin.tenants.show', $tenant)
                ->with('success', $message);
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', trans('admin::app.tenants.create.error', [
                    'message' => $e->getMessage(),
                ]));
        }
    }

    public function show(Tenant $tenant): View
    {
        $tenant->load(['database', 'domains']);
        
        // Load merchant user (admin credentials)
        $merchantUser = \App\Models\MerchantUser::where('tenant_id', $tenant->id)->first();

        return view('admin.tenants.show', compact('tenant', 'merchantUser'));
    }

    public function retryProvisioning(Tenant $tenant): RedirectResponse
    {
        $merchant = MerchantUser::query()->where('tenant_id', $tenant->id)->first();

        if (! $merchant) {
            return back()->with('error', 'No merchant user found for tenant.');
        }

        $tenant->forceFill([
            'status' => 'provisioning',
            'provisioning_started_at' => now(),
            'last_error' => null,
        ])->save();

        dispatch(new ProvisionTenantJob(
            tenantId: $tenant->id,
            adminEmail: $merchant->email,
            adminPasswordHash: $merchant->password,
            adminName: $merchant->name
        ));

        return back()->with('success', 'Provisioning job dispatched.');
    }

    public function toggleStatus(Tenant $tenant): RedirectResponse
    {
        $tenant->status = $tenant->status === 'active' ? 'inactive' : 'active';
        $tenant->save();

        return back()->with('success', 'Tenant status updated.');
    }

    public function rotateDomainToken(Domain $domain, DomainVerificationService $service): RedirectResponse
    {
        $service->start($domain, $domain->verification_method ?: DomainVerificationService::METHOD_DNS_TXT);

        return back()->with('success', 'Domain token rotated.');
    }

    public function verifyDomain(Domain $domain, Request $request, DomainVerificationService $service): RedirectResponse
    {
        $request->validate([
            'method' => ['nullable', 'in:dns_txt,http_file'],
        ]);

        $result = $service->attemptVerify($domain, $request->input('method'));

        if (! $result['ok']) {
            return back()->with('error', 'Verify failed: ' . ($result['reason'] ?? 'unknown'));
        }

        return back()->with('success', 'Domain verified.');
    }
}
