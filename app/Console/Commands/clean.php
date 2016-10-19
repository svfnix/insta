<?php

namespace App\Console\Commands;

use App\Console\Instagram;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class clean extends Command
{

    protected $signature = 'insta:clean';
    protected $description = 'Clean mutual follows';

    public function handle()
    {

        $instagram = new Instagram();
        $response = json_decode($instagram->login());
        if(!$response || !$response->authenticated){
            $this->error('Login Failed!');
        }

        $follows = DB::table('queues')
                        ->whereDate('created_at', '<=', Carbon::create()->subDays(7))
                        ->whereNotNull('followed_at')
                        ->whereNull('unfollowed_at')
                        ->orderBy('last_check_at', 'asc')
                        ->limit(10)
                        ->get();

        foreach($follows as $follow){
            if(DB::table('followers')->find($follow->id)) {
                $this->info('User already is your follower');
                DB::table('queues')->where('id',  $follow->id)->update(['last_check_at' => new \DateTime()]);
            } else {
                $this->error('User is not your follower and be removed ...');
                DB::table('queues')->where('id',  $follow->id)->update(['unfollowed_at' => new \DateTime()]);
                $instagram->unfollow($follow->id);
            }
        }

    }
}
