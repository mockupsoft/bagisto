<?php

namespace MockupSoft\Companies\Providers;

use Webkul\Core\Providers\CoreModuleServiceProvider;

class ModuleServiceProvider extends CoreModuleServiceProvider
{
    /**
     * Models to be registered by Concord.
     * Patch 2'de Contracts eklendiğinde map kurulacak:
     * \MockupSoft\Companies\Contracts\Company::class => \MockupSoft\Companies\Models\Company::class
     *
     * @var array
     */
    protected $models = [];

    /**
     * Register services.
     */
    public function register(): void
    {
        parent::register();

        $this->app->register(CompaniesServiceProvider::class);
    }
}
