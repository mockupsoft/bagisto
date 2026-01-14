<?php

namespace App\Http\Controllers;

use App\Models\Tenant\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class ProvisioningController extends Controller
{
    protected const SESSION_KEY = 'onboarding.merchant_register';

    public function progress(Tenant $tenant): View
    {
        $this->authorizeSession($tenant);

        return view('provisioning.progress', [
            'tenant' => $tenant,
            'statusUrl' => route('provisioning.status', ['tenant' => $tenant->id]),
        ]);
    }

    public function status(Tenant $tenant): JsonResponse
    {
        $this->authorizeSession($tenant);

        $tenantDb = $tenant->database;
        $status = $tenant->status;
        $dbStatus = $tenantDb?->status;
        $percent = $this->percentFor($status, $dbStatus);

        $message = match ($status) {
            'active' => 'Provisioning complete.',
            'ready' => 'Provisioning complete.',
            'failed' => 'Provisioning failed.',
            'provisioning' => 'Provisioning in progress.',
            'pending' => 'Provisioning requested.',
            default => 'Provisioning state unknown.',
        };

        $lastError = $tenant->last_error ?? $tenantDb?->last_error;

        return response()->json([
            'status' => $status,
            'db_status' => $dbStatus,
            'percent' => $percent,
            'message' => $message,
            'last_error' => $lastError,
        ]);
    }

    protected function authorizeSession(Tenant $tenant): void
    {
        $sessionTenantId = (int) session(self::SESSION_KEY . '.tenant_id');

        if ($sessionTenantId !== (int) $tenant->id) {
            abort(403);
        }
    }

    protected function percentFor(?string $tenantStatus, ?string $dbStatus): int
    {
        $base = match ($tenantStatus) {
            'active' => 100,
            'ready' => 100,
            'failed' => 100,
            'provisioning' => 60,
            'pending' => 20,
            default => 10,
        };

        if ($dbStatus === 'ready') {
            $base = max($base, 90);
        } elseif ($dbStatus === 'provisioning') {
            $base = max($base, 60);
        } elseif ($dbStatus === 'failed') {
            $base = 100;
        }

        return $base;
    }
}
