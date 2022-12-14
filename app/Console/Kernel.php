<?php

namespace App\Console;

use App\Console\Commands\AlertTree;
use App\Console\Commands\AlertTwo;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        AlertTwo::class,
        AlertTree::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('alert:two')->cron('1 00 */1 * *');
        // $schedule->command('alert:tree')->cron('1 00 */2 * *');
        $schedule->command('alert:two')->everyFiveMinutes();
        $schedule->command('alert:tree')->everyTenMinutes();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
        // require base_path('routes/console.php');
    }
}
