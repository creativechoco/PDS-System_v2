<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Check for inactive users daily at 2 AM
        $schedule->command('users:check-inactive')
            ->daily()
            ->at('02:00')
            ->description('Automatically archive users inactive for 5+ years');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        
        // Explicitly register the command for scheduler
        $this->commands = [
            \App\Console\Commands\CheckInactiveUsers::class,
        ];
    }
}
