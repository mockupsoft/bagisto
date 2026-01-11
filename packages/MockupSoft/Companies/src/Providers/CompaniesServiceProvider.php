<?php

namespace MockupSoft\Companies\Providers;

use Illuminate\Support\ServiceProvider;

class CompaniesServiceProvider extends ServiceProvider
{
    /**
     * Register application services.
     * Config merge must be in register() - Bagisto pattern.
     */
    public function register(): void
    {
        $this->registerConfig();
    }

    /**
     * Bootstrap application services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'mockupsoft-companies');

        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'mockupsoft-companies');

        $this->loadRoutesFrom(__DIR__.'/../Routes/admin-routes.php');
    }

    /**
     * Register package config.
     */
    protected function registerConfig(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/acl.php', 'acl');

        $this->mergeConfigFrom(__DIR__.'/../Config/menu.php', 'menu.admin');
    }
}
