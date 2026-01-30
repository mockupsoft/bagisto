<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Models\MerchantUser;
use App\Models\Tenant\Domain;
use App\Models\Tenant\Tenant;
use App\Services\Tenant\DomainVerificationService;
use App\Services\Tenant\TenantProvisioner;
use App\Support\Tenant\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Tests\Support\TenantTestContext;
use Illuminate\View\View;

class MerchantDashboardController extends Controller
{
    public function index(DomainVerificationService $verificationService): View
    {
        /** @var MerchantUser $merchant */
        $merchant = auth()->guard('merchant')->user();

        $tenant = Tenant::query()
            ->with(['database', 'domains'])
            ->findOrFail($merchant->tenant_id);

        $tenantDb = $tenant->database;
        $stats = [];

        if ($tenantDb && $tenantDb->status === 'ready') {
            TenantTestContext::setTenantContext($tenant, $tenantDb);
            
            try {
                $stats = [
                    'total_orders' => DB::connection('tenant')->table('orders')->count(),
                    'today_orders' => DB::connection('tenant')->table('orders')->whereDate('created_at', today())->count(),
                    'month_orders' => DB::connection('tenant')->table('orders')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
                    'total_revenue' => DB::connection('tenant')->table('orders')->where('status', '!=', 'canceled')->sum('base_grand_total'),
                    'month_revenue' => DB::connection('tenant')->table('orders')->where('status', '!=', 'canceled')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->sum('base_grand_total'),
                    'total_customers' => DB::connection('tenant')->table('customers')->count(),
                    'total_products' => DB::connection('tenant')->table('products')->count(),
                    'recent_orders' => DB::connection('tenant')->table('orders')->orderBy('created_at', 'desc')->limit(5)->get(),
                ];
            } finally {
                TenantTestContext::clearTenantContext();
            }
        }

        return view('merchant.dashboard', [
            'tenant' => $tenant,
            'domains' => $tenant->domains,
            'dnsPrefix' => DomainVerificationService::DNS_PREFIX,
            'httpPath' => DomainVerificationService::HTTP_WELL_KNOWN_PATH,
            'verificationService' => $verificationService,
            'stats' => $stats,
        ]);
    }

    public function addDomain(Request $request, TenantProvisioner $provisioner, DomainVerificationService $verificationService): RedirectResponse
    {
        /** @var MerchantUser $merchant */
        $merchant = auth()->guard('merchant')->user();

        $validated = $request->validate([
            'domain' => ['required', 'string', 'min:4'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $tenant = Tenant::findOrFail($merchant->tenant_id);

        $domain = $provisioner->attachCustomDomain($tenant, $validated['domain'], createdById: null, note: $validated['note'] ?? null);
        $verificationService->start($domain, DomainVerificationService::METHOD_DNS_TXT);

        return redirect()->route('merchant.dashboard')->with('success', 'Domain added.');
    }

    public function rotateToken(Domain $domain, Request $request, DomainVerificationService $service): RedirectResponse
    {
        $this->authorizeTenantDomain($domain);

        $request->validate([
            'method' => ['nullable', 'in:dns_txt,http_file'],
        ]);

        $service->start($domain, $request->input('method') ?: ($domain->verification_method ?: DomainVerificationService::METHOD_DNS_TXT));

        return redirect()->route('merchant.dashboard')->with('success', 'Verification token rotated.');
    }

    public function verifyNow(Domain $domain, Request $request, DomainVerificationService $service): RedirectResponse
    {
        $this->authorizeTenantDomain($domain);

        $request->validate([
            'method' => ['nullable', 'in:dns_txt,http_file'],
        ]);

        /** @var MerchantUser $merchant */
        $merchant = auth()->guard('merchant')->user();

        $domainKey = 'domain-verify:domain:' . $domain->id;
        $merchantKey = 'domain-verify:merchant:' . $merchant->id;

        if (RateLimiter::tooManyAttempts($domainKey, 10) || RateLimiter::tooManyAttempts($merchantKey, 25)) {
            return redirect()->route('merchant.dashboard')->with('error', 'Rate limited. Please try again later.');
        }

        RateLimiter::hit($domainKey, 60);
        RateLimiter::hit($merchantKey, 60);

        $result = $service->attemptVerify($domain, $request->input('method'));

        if (! $result['ok']) {
            return redirect()->route('merchant.dashboard')->with('error', 'Verification failed: ' . ($result['reason'] ?? 'unknown'));
        }

        return redirect()->route('merchant.dashboard')->with('success', 'Domain verified.');
    }

    protected function authorizeTenantDomain(Domain $domain): void
    {
        /** @var MerchantUser $merchant */
        $merchant = auth()->guard('merchant')->user();

        if ((int) $domain->tenant_id !== (int) $merchant->tenant_id) {
            abort(403);
        }
    }
}
