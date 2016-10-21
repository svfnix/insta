<?php

namespace App\Console\Commands;

use App\Console\Instagram;
use App\Queue;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class crawl extends Command
{
    protected $signature = 'insta:crawl';
    protected $description = 'Crawl queue users followers';

    public function handle()
    {
        $this->info('Fetch user from queue.');

        $user = DB::table('queues')
                ->whereNull('crawled_at')
                ->orderBy('cycle', 'asc')
                ->orderBy('created_at', 'asc')
                ->first();

        if($user){

            $this->info('Fetching user '.$user->id.' ['.$user->username.']');

            $instagram = new Instagram();

            DB::table('queues')->where('id',  $user->id)->update(['crawled_at' => new \DateTime()]);

            $count = 1;
            $page = 1;

            $this->info('start retrieving user followers');
            $response = json_decode($instagram->getFollowers($user->id));

            $counter = 0;
            while($response && $response->status == 'ok'){

                foreach($response->followed_by->nodes as $node){
                    if(DB::table('queues')->where('id', $node->id)->first()) {
                        $this->warn((++$counter) . ') User already exists '. $node->id .' ['. $node->username .': '. $node->full_name .']');
                    } else {
                        $queue = new Queue();
                        $queue->id = $node->id;
                        $queue->username = $node->username;
                        $queue->created_at = new \DateTime();
                        $queue->cycle = $user->cycle + 1;
                        $queue->save();

                        $this->info((++$counter) . ') Follower updated : ' . $node->id .' ['. $node->username .': '. $node->full_name .']');
                        $count++;
                    }
                }

                if(empty($response->followed_by->page_info->end_cursor)) {
                    break;
                }

                $this->info('retrieving followers - page '. ($page++) . ' of '. floor($response->followed_by->count / $instagram->page_count_follow));
                $response = json_decode($instagram->getFollowers($instagram->getUserID(), $response->followed_by->page_info->end_cursor));
            }

            $this->info($count . ' follower updated.');
        } else {
            $this->error('Queue is empty!');
        }
    }
}
