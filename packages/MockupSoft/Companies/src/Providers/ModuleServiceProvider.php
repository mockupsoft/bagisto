<?php

namespace MockupSoft\Companies\Providers;

use Webkul\Core\Providers\CoreModuleServiceProvider;

class ModuleServiceProvider extends CoreModuleServiceProvider
{
    /**
     * Models to be registered by Concord.
     * Contract => Model mapping for Concord proxy system.
     *
     * @var array
     */
    protected $models = [
        \MockupSoft\Companies\Contracts\Company::class => \MockupSoft\Companies\Models\Company::class,
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        parent::register();

        $this->app->register(CompaniesServiceProvider::class);
    }
}
