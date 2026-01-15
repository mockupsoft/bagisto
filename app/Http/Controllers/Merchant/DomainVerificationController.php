<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Domain;
use App\Models\Tenant\Tenant;
use App\Services\Tenant\DomainVerificationService;
use App\Services\Tenant\TenantProvisioner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class DomainVerificationController extends Controller
{
    protected const SESSION_KEY = 'onboarding.merchant_register';

    public function store(Request $request, TenantProvisioner $provisioner, DomainVerificationService $verificationService): JsonResponse
    {
        $tenantId = (int) session(self::SESSION_KEY . '.tenant_id');

        if (! $tenantId) {
            abort(403);
        }

        $tenant = Tenant::findOrFail($tenantId);

        $request->validate([
            'domain' => ['required', 'string', 'min:4'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $normalized = $verificationService->normalizeDomain($request->string('domain')->toString());

        $existingVerified = Domain::query()
            ->where('domain', $normalized)
            ->whereNotNull('verified_at')
            ->where('tenant_id', '!=', $tenant->id)
            ->exists();

        if ($existingVerified) {
            return response()->json([
                'ok' => false,
                'reason' => 'domain_already_verified',
            ], 409);
        }

        $domain = $provisioner->attachCustomDomain($tenant, $normalized, createdById: null, note: $request->input('note'));
        $domain = $verificationService->start($domain, DomainVerificationService::METHOD_DNS_TXT);

        return response()->json([
            'ok' => true,
            'domain' => $domain,
        ]);
    }

    public function instructions(Domain $domain, DomainVerificationService $service): JsonResponse
    {
        $this->authorizeSession($domain);

        $dns = $service->getDnsInstruction($domain);
        $http = $service->getHttpInstruction($domain);

        return response()->json([
            'id' => $domain->id,
            'domain' => $domain->domain,
            'verified_at' => $domain->verified_at,
            'method' => $domain->verification_method,
            'dns_txt' => $dns,
            'http_file' => $http,
        ]);
    }

    public function status(Domain $domain): JsonResponse
    {
        $this->authorizeSession($domain);

        return response()->json([
            'id' => $domain->id,
            'domain' => $domain->domain,
            'tenant_id' => $domain->tenant_id,
            'type' => $domain->type,
            'verified_at' => $domain->verified_at,
            'verification_method' => $domain->verification_method,
            'verification_started_at' => $domain->verification_started_at,
            'last_checked_at' => $domain->last_checked_at,
            'last_failure_reason' => $domain->last_failure_reason,
        ]);
    }

    public function rotateToken(Domain $domain, DomainVerificationService $service, Request $request): JsonResponse
    {
        $this->authorizeSession($domain);

        $request->validate([
            'method' => ['nullable', 'in:dns_txt,http_file'],
        ]);

        $domain = $service->start($domain, $request->input('method') ?: ($domain->verification_method ?: 'dns_txt'));

        return response()->json([
            'ok' => true,
            'domain' => $domain,
        ]);
    }

    public function verify(Domain $domain, DomainVerificationService $service, Request $request): JsonResponse
    {
        $this->authorizeSession($domain);

        $request->validate([
            'method' => ['nullable', 'in:dns_txt,http_file'],
        ]);

        $tenantId = (int) session(self::SESSION_KEY . '.tenant_id');

        $domainKey = 'domain-verify:domain:' . $domain->id;
        $tenantKey = 'domain-verify:tenant:' . $tenantId;

        if (RateLimiter::tooManyAttempts($domainKey, 10) || RateLimiter::tooManyAttempts($tenantKey, 25)) {
            return response()->json([
                'ok' => false,
                'reason' => 'rate_limited',
            ], 429);
        }

        RateLimiter::hit($domainKey, 60);
        RateLimiter::hit($tenantKey, 60);

        if ($domain->type !== 'custom') {
            return response()->json([
                'ok' => false,
                'reason' => 'not_custom',
            ], 422);
        }

        // Takeover protection: if a verified domain exists for another tenant, block.
        $normalized = $service->normalizeDomain($domain->domain);

        $claimed = Domain::query()
            ->where('domain', $normalized)
            ->whereNotNull('verified_at')
            ->where('tenant_id', '!=', $domain->tenant_id)
            ->exists();

        if ($claimed) {
            return response()->json([
                'ok' => false,
                'reason' => 'domain_already_verified',
            ], 409);
        }

        $result = $service->attemptVerify($domain, $request->input('method'));

        if (! $result['ok']) {
            return response()->json([
                'ok' => false,
                'method' => $result['method'],
                'reason' => $result['reason'],
            ], 422);
        }

        return response()->json([
            'ok' => true,
            'method' => $result['method'],
        ]);
    }

    protected function authorizeSession(Domain $domain): void
    {
        $tenantId = (int) session(self::SESSION_KEY . '.tenant_id');

        if (! $tenantId || $tenantId !== (int) $domain->tenant_id) {
            abort(403);
        }
    }
}
