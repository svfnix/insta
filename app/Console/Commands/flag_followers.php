<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class flag_followers extends Command
{
    protected $signature = 'insta:flag_followers';
    protected $description = 'Command description';

    public function handle()
    {
        $followers = DB::table('followers')->get();
        foreach($followers as $follower){
            $this->info('Flag follower : ' . $follower->id .' ['. $follower->username .']');
            DB::table('queues')->where('id', $follower->id)->update(['followed_at' => new \DateTime()]);
        }
    }
}
