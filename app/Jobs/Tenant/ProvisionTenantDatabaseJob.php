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

        $db->status = 'provisioning';
        $db->last_error = null;
        $db->save();

        try {
            $provisioner = app(TenantDatabaseProvisioner::class);
            $result = $provisioner->provision($db);

            if ($result['ok']) {
                $db->status = 'ready';
                $db->last_error = null;
            } else {
                $db->status = 'failed';
                $db->last_error = $result['reason'];
            }
        } catch (Throwable $e) {
            $db->status = 'failed';
            $db->last_error = mb_strimwidth($e->getMessage(), 0, 1000);
            report($e);
        }

        $db->save();
    }
}
