<?php

namespace MockupSoft\Companies\Providers;

use Illuminate\Support\ServiceProvider;

class CompaniesServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap application services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'mockupsoft-companies');

        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'mockupsoft-companies');

        $this->loadOptionalRoutes(__DIR__.'/../Routes/admin-routes.php');
    }

    /**
     * Register application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Load routes from file if it exists.
     */
    protected function loadOptionalRoutes(string $path): void
    {
        if (is_file($path)) {
            $this->loadRoutesFrom($path);
        }
    }
}
