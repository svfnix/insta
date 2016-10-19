<?php

namespace App\Console;

use App\Console\Commands\clean;
use App\Console\Commands\crawl;
use App\Console\Commands\fetch;
use App\Console\Commands\flag_followers;
use App\Console\Commands\followers;
use App\Console\Commands\follows;
use App\Console\Commands\send_request;
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
        followers::class,
        follows::class,
        fetch::class,
        crawl::class,
        flag_followers::class,
        send_request::class,
        clean::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
