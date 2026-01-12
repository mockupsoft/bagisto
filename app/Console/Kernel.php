<?php

namespace App\Console;

use App\Console\Commands\ProvisionTenantCommand;
use App\Console\Commands\ProvisionTenantDatabaseCommand;
use App\Console\Commands\VerifyDomainCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array<int, class-string>
     */
    protected $commands = [
        ProvisionTenantCommand::class,
        VerifyDomainCommand::class,
        ProvisionTenantDatabaseCommand::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        //
    }
}
