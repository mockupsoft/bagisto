<?php

namespace App\Services\Tenant;

use App\Support\Tenant\TenantRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class TenantConnectionSelector
{
    public function apply(Model $model): Model
    {
        if (! TenantRequest::isTenantResolved()) {
            return $model;
        }

        $class = get_class($model);

        $tenantNamespaces = Config::get('tenant-router.tenant_namespaces', []);
        $globalNamespaces = Config::get('tenant-router.global_namespaces', []);

        foreach ($globalNamespaces as $ns) {
            if (str_starts_with($class, $ns)) {
                return $model;
            }
        }

        foreach ($tenantNamespaces as $ns) {
            if (str_starts_with($class, $ns)) {
                $model->setConnection('tenant');
                return $model;
            }
        }

        return $model;
    }
}
