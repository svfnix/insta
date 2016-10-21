<?php

namespace App\Console\Commands;

use App\Console\Instagram;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class clean extends Command
{

    protected $signature = 'insta:clean';
    protected $description = 'Clean mutual follows';

    public function handle()
    {

        //Artisan::call('insta:followers');
        $limit = 5;
        $count = 0;
        $instagram = new Instagram();

        $follows = DB::table('queues')
                        ->whereDate('created_at', '<=', Carbon::create()->subHours(24))
                        ->whereNotNull('followed_at')
                        ->whereNull('unfollowed_at')
                        ->orderBy('last_check_at', 'asc')
                        ->limit(100)
                        ->get();

        $counter = 0;
        foreach($follows as $follow){

            if(DB::table('followers')->find($follow->id)) {
                $this->warn((++$counter) . ') User already is your follower '. $follow->id .' ['. $follow->username .']');
                DB::table('queues')->where('id',  $follow->id)->update(['last_check_at' => new \DateTime()]);
            } else {
                $this->info((++$counter) . ') User is not your follower and is going to removed ... '. $follow->id .' ['. $follow->username .']');
                DB::table('queues')->where('id',  $follow->id)->update(['unfollowed_at' => new \DateTime()]);
                $instagram->unfollow($follow->id);
                $count++;
            }

            if($count >= $limit){
                return;
            }
        }

    }
}
