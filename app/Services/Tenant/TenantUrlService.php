<?php

namespace App\Services\Tenant;

use App\Models\Tenant\Tenant;
use Illuminate\Support\Facades\Config;

class TenantUrlService
{
    /**
     * Get tenant admin panel URL.
     */
    public function getAdminUrl(Tenant $tenant): ?string
    {
        $domain = $this->getPrimaryDomain($tenant);
        
        if (! $domain) {
            return null;
        }
        
        $protocol = $this->getProtocol();
        $adminPath = Config::get('app.admin_url', 'admin');
        
        return "{$protocol}://{$domain}/{$adminPath}";
    }

    /**
     * Get tenant storefront URL.
     */
    public function getStorefrontUrl(Tenant $tenant): ?string
    {
        $domain = $this->getPrimaryDomain($tenant);
        
        if (! $domain) {
            return null;
        }
        
        $protocol = $this->getProtocol();
        
        return "{$protocol}://{$domain}/";
    }

    /**
     * Get primary domain for tenant.
     */
    public function getPrimaryDomain(Tenant $tenant): ?string
    {
        $tenant->loadMissing('primaryDomain');
        
        $primaryDomain = $tenant->primaryDomain;
        
        if (! $primaryDomain) {
            return null;
        }
        
        // For custom domains, only return if verified
        if ($primaryDomain->type === 'custom' && ! $primaryDomain->verified_at) {
            return null;
        }
        
        return $primaryDomain->domain;
    }

    /**
     * Check if tenant has accessible domain.
     */
    public function hasAccessibleDomain(Tenant $tenant): bool
    {
        return $this->getPrimaryDomain($tenant) !== null;
    }

    /**
     * Get protocol (http/https) based on environment.
     */
    protected function getProtocol(): string
    {
        // Check if we're in local development
        if (app()->environment('local')) {
            // Check if APP_URL contains https
            $appUrl = Config::get('app.url', '');
            if (str_starts_with($appUrl, 'https://')) {
                return 'https';
            }
            return 'http';
        }
        
        // Production: default to https
        return 'https';
    }
}
