<?php

namespace App\Console\Commands;

use App\Console\Instagram;
use App\Follower;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class followers extends Command
{
    protected $signature = 'insta:followers';
    protected $description = 'update followers list';

    public function handle()
    {
        $instagram = new Instagram();
        $response = json_decode($instagram->login());
        if(!$response || !$response->authenticated){
            $this->error('Login Failed!');
        }

        $count = 1;
        $page = 1;

        $this->info('Clear followers list');
        DB::table('followers')->truncate();

        $this->info('start retrieving followers');
        $response = json_decode($instagram->getFollowers($instagram->userid));
        while($response && $response->status == 'ok'){

            foreach($response->followed_by->nodes as $node){
                $follower = new Follower();
                $follower->id = $node->id;
                $follower->username = $node->username;
                $follower->created_at = new \DateTime();
                $follower->save();

                $this->info('Follower updated : '. $node->id .' ['. $node->username .': '. $node->full_name .']');
                $count++;
            }

            if(empty($response->followed_by->page_info->end_cursor)) {
                break;
            }

            $this->info('retrieving followers - page '. ($page++) . ' of '. floor($response->followed_by->count / $instagram->page_count));
            $response = json_decode($instagram->getFollowers($instagram->userid, $response->followed_by->page_info->end_cursor));
        }

        $this->info($count . ' follower updated.');
    }
}
