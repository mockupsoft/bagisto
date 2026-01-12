<?php

namespace App\Jobs\Tenant;

use App\Models\Tenant\TenantDatabase;
use App\Services\Tenant\TenantDatabaseProvisioner;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProvisionTenantDatabaseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $tenantId)
    {
    }

    public function handle(): void
    {
        $db = TenantDatabase::where('tenant_id', $this->tenantId)
            ->whereNull('deleted_at')
            ->first();

        if (! $db) {
            Log::warning('ProvisionTenantDatabaseJob: tenant database not found', ['tenant_id' => $this->tenantId]);
            return;
        }

        if ($db->status === 'ready') {
            return;
        }

        try {
            $provisioner = app(TenantDatabaseProvisioner::class);
            $result = $provisioner->provision($db);

            if (! $result['ok']) {
                Log::error('ProvisionTenantDatabaseJob failed', [
                    'tenant_id' => $this->tenantId,
                    'reason' => $result['reason'],
                ]);
            }
        } catch (Throwable $e) {
            report($e);
        }
    }
}
