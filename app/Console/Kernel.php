<?php

namespace App\Console;

use App\Console\Commands\accept_request;
use App\Console\Commands\clean;
use App\Console\Commands\crawl;
use App\Console\Commands\fetch;
use App\Console\Commands\flag_follows;
use App\Console\Commands\followers;
use App\Console\Commands\follows;
use App\Console\Commands\like;
use App\Console\Commands\like_all;
use App\Console\Commands\login;
use App\Console\Commands\send_request;
use App\Console\Commands\send_request_live;
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
        login::class,
        followers::class,
        follows::class,
        fetch::class,
        crawl::class,
        flag_follows::class,
        send_request::class,
        send_request_live::class,
        accept_request::class,
        clean::class,
        like::class,
        like_all::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('insta:accept_request')->everyFiveMinutes();
        //$schedule->command('insta:send_request_live')->everyMinute();
        $schedule->command('insta:like')->everyFiveMinutes();
        $schedule->command('insta:crawl')->everyFiveMinutes();
        $schedule->command('insta:clean')->everyFiveMinutes();
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
