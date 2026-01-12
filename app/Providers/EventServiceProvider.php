<?php

namespace App\Providers;

use App\Events\Tenant\TenantProvisioningRequested;
use App\Listeners\Tenant\DispatchTenantProvisioningJob;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        TenantProvisioningRequested::class => [
            DispatchTenantProvisioningJob::class,
        ],
    ];
}
