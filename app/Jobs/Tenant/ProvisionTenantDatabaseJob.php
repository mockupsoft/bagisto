<?php

namespace App\Jobs\Tenant;

use App\Models\Tenant\TenantDatabase;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProvisionTenantDatabaseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $tenantId)
    {
    }

    public function handle(): void
    {
        $db = TenantDatabase::where('tenant_id', $this->tenantId)->first();

        if (! $db) {
            Log::warning('ProvisionTenantDatabaseJob: tenant database not found', ['tenant_id' => $this->tenantId]);
            return;
        }

        // Stub: no real DB creation yet. Just heartbeat.
        $db->touch();
        Log::info('ProvisionTenantDatabaseJob heartbeat', ['tenant_id' => $this->tenantId]);
    }
}
