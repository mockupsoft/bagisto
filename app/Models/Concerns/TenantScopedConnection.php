<?php

namespace App\Models\Concerns;

use App\Support\Tenant\TenantRequest;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Model;

trait TenantScopedConnection
{
    public function getConnectionName()
    {
        if ($this->usesTenantConnection()) {
            return 'tenant';
        }

        return parent::getConnectionName();
    }

    protected function usesTenantConnection(): bool
    {
        if (! TenantRequest::isTenantResolved()) {
            return false;
        }

        $class = static::class;

        $tenantNamespaces = Config::get('tenant-router.tenant_namespaces', []);

        foreach ($tenantNamespaces as $ns) {
            if (str_starts_with($class, $ns)) {
                return true;
            }
        }

        return false;
    }
}
