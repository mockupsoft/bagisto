<?php

namespace App\Support\Tenant;

use App\Models\Tenant\Domain;
use App\Models\Tenant\Tenant;

class TenantContext
{
    protected ?Tenant $tenant = null;

    protected ?Domain $domain = null;

    public function setTenant(Tenant $tenant): void
    {
        $this->tenant = $tenant;
    }

    public function tenant(): ?Tenant
    {
        return $this->tenant;
    }

    public function setDomain(Domain $domain): void
    {
        $this->domain = $domain;
    }

    public function domain(): ?Domain
    {
        return $this->domain;
    }

    public function clear(): void
    {
        $this->tenant = null;
        $this->domain = null;
    }
}
