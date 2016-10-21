<?php

namespace App\Console\Commands;

use App\Console\Instagram;
use App\Follow;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class follows extends Command
{

    protected $signature = 'insta:follows';
    protected $description = 'Update follows list';

    public function handle()
    {
        $instagram = new Instagram();
        $response = json_decode($instagram->login());
        if(!$response || !$response->authenticated){
            $this->error('Login Failed!');
        }

        $count = 1;
        $page = 1;

        $this->info('Clear follows list');
        DB::table('follows')->truncate();

        $this->info('start retrieving follows list');
        $response = json_decode($instagram->getFollows($instagram->userid));

        $counter = 0;
        while($response && $response->status == 'ok'){

            foreach($response->follows->nodes as $node){
                $follow = new Follow();
                $follow->id = $node->id;
                $follow->username = $node->username;
                $follow->created_at = new \DateTime();
                $follow->save();

                $this->info((++$counter) . ') Follows list updated : '. $node->id .' ['. $node->username .': '. $node->full_name .']');
                $count++;
            }

            if(empty($response->follows->page_info->end_cursor)) {
                break;
            }

            $this->info('retrieving follows list - page '. ($page++) . ' of '. floor($response->follows->count / $instagram->page_count));
            $response = json_decode($instagram->getFollows($instagram->userid, $response->follows->page_info->end_cursor));
        }

        $this->info($count . ' follows list updated.');
    }
}
