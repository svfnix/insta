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
        DB::table('queues')->whereNotNull('followed_at')->update(['followed_at' => null]);
        
        $followers = DB::table('followers')->get();

        $counter = 0;
        foreach($followers as $follower){
            $this->info((++$counter) . ') Flag follower : ' . $follower->id .' ['. $follower->username .']');
            DB::table('queues')->where('id', $follower->id)->update(['followed_at' => new \DateTime()]);
        }
    }
}
