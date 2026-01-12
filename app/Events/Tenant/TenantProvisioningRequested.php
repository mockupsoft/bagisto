<?php

namespace App\Events\Tenant;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TenantProvisioningRequested
{
    use Dispatchable, SerializesModels;

    public function __construct(public int $tenantId)
    {
    }
}
