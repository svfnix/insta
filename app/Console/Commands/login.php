<?php

namespace App\Console\Commands;

use App\Console\Instagram;
use App\Queue;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class login extends Command
{

    protected $signature = 'insta:login';
    protected $description = 'Like home posts';

    public function handle()
    {
        $instagram = new Instagram();
        $this->info($instagram->login());
    }
}
