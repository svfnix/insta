<?php

namespace App\Console\Commands;

use App\Console\Instagram;
use App\Queue;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class send_request_live extends Command
{
    protected $signature = 'insta:send_request_live';
    protected $description = 'Send follow request live data';

    public function handle()
    {
        $limit = 5;
        $count = 0;
        $counter = 0;

        $this->info('Start following likers');

        $instagram = new Instagram();
        $nodes = $instagram->getUserUpdates();
        foreach($nodes as $node){
            if($node->likes->count){
                foreach ($node->likes->nodes as $like){
                    if($like->user->id != $instagram->getUserID()){
                        $user = DB::table('queues')->where('id', $like->user->id)->first();
                        if($user){
                            if(is_null($user->followed_at)){
                                $this->info((++$counter) . ') Follow liker/user : ' . $user->id .' ['. $user->username .']');
                                DB::table('queues')->where('id',  $user->id)->update(['followed_at' => new \DateTime()]);
                                $instagram->follow($user->id);
                                $count++;
                            }
                        }else{
                            $this->info((++$counter) . ') Follow liker : ' . $like->user->id .' ['. $like->user->username .']');

                            $queue = new Queue();
                            $queue->id = $like->user->id;
                            $queue->username = $like->user->username;
                            $queue->created_at = new \DateTime();
                            $queue->followed_at = new \DateTime();
                            $queue->cycle = -1;
                            $queue->save();

                            $instagram->follow($like->user->id);
                            $count++;
                        }

                        if($count >= $limit){
                            return;
                        }
                    }
                }
            }
        }

        $this->info('Start following users');

        $queues = DB::table('queues')->whereNull('followed_at')->limit( $limit - $count )->get();
        foreach($queues as $queue){
            $this->info((++$counter) . ') Follow user : ' . $queue->id .' ['. $queue->username .']');
            DB::table('queues')->where('id',  $queue->id)->update(['followed_at' => new \DateTime()]);
            $instagram->follow($queue->id);
        }
    }
}
