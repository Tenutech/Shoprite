<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('shortlist:expiry')->dailyAt('02:00');
        $schedule->command('reminders:send')->dailyAt('05:00');
        $schedule->command('vacancy:delete_no_interview')->dailyAt('02:30');
        $schedule->command('vacancy:delete_no_appointment')->dailyAt('03:00');
        $schedule->command('talentpool:fixed_term')->dailyAt('03:15');
        $schedule->command('talentpool:peak_season')->dailyAt('03:30');
        $schedule->command('talentpool:YES')->dailyAt('03:45');
        $schedule->command('talentpool:RRP')->dailyAt('04:00');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
