<?php

namespace App\Providers;

use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\ParallelTesting;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Override Webkul Core with App Core for tenant-safe channel resolution
        $this->app->singleton(
            \Webkul\Core\Core::class,
            \App\Core\Core::class
        );

        $allowedIPs = array_map('trim', explode(',', config('app.debug_allowed_ips')));

        $allowedIPs = array_filter($allowedIPs);

        if (empty($allowedIPs)) {
            return;
        }

        if (in_array(Request::ip(), $allowedIPs)) {
            Debugbar::enable();
        } else {
            Debugbar::disable();
        }
    }

    /**
     * Register tenant management configuration.
     */
    protected function registerTenantConfig(): void
    {
        // Merge menu config - append to existing menu.admin array
        $tenantMenu = require base_path('config/menu.php');
        $existingMenu = config('menu.admin', []);
        
        // Ensure tenants menu is added
        $mergedMenu = array_merge($existingMenu, $tenantMenu['admin'] ?? []);
        config(['menu.admin' => $mergedMenu]);

        // Merge ACL config - append to existing acl array
        $tenantAcl = require base_path('config/acl.php');
        $existingAcl = config('acl', []);
        
        // Ensure tenants ACL is added
        $mergedAcl = array_merge($existingAcl, $tenantAcl);
        config(['acl' => $mergedAcl]);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register tenant management menu and ACL (in boot to ensure all configs are loaded)
        $this->registerTenantConfig();

        ParallelTesting::setUpTestDatabase(function (string $database, int $token) {
            Artisan::call('db:seed');
        });
    }
}
